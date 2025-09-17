/**
 * @type: saga
 * name: core.presentItemMenu
 */
import {
  getGlobalContext,
  getItem,
  getItemMenu,
  getSession,
  ItemLocalAction,
  MenuShape
} from '@metafox/framework';
import { filterShowWhen } from '@metafox/utils';
import { put, take, takeEvery } from 'redux-saga/effects';

export function* presentItemMenu({
  payload,
  meta
}: ItemLocalAction<
  {
    identity: string;
    menuName: string;
    dependName?: string;
  },
  {
    setMenu: (data: any) => void;
  }
>) {
  const { identity, menuName, dependName } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  if (!meta?.setMenu || !item) return;

  let data: Record<string, any>;

  if (dependName) {
    const dependActionName = `${dependName}/context`;

    yield put({
      type: dependName,
      payload: { item, dependActionName }
    });
    const action = yield take(dependActionName);

    data = action.payload;
  }

  const menu: MenuShape = yield* getItemMenu(item, menuName);
  const session = yield* getSession();
  const { acl, setting, getPageParams } = yield* getGlobalContext();
  const { profile_id, profile_type, identity: subjectId } = getPageParams();

  const subject = yield* getItem(subjectId);

  if (!data)
    data = {
      item,
      acl,
      setting,
      session,
      profile_id,
      profile_type,
      subject
    };

  if (!data.item) data.item = item;

  if (!data.session) data.session = session;

  if (!data.subject) data.subject = subject;

  if (!menu?.items) return;

  const items = filterShowWhen(menu.items, data);

  if (meta?.setMenu) {
    meta.setMenu(items);
  }
}

const sagaEffect = [takeEvery('presentItemMenu', presentItemMenu)];

export default sagaEffect;
