/**
 * @type: saga
 * name: core.presentAppMenu
 */
import {
  getAppMenu,
  getGlobalContext,
  getItem,
  getSession,
  LocalAction,
  MenuShape
} from '@metafox/framework';
import { filterShowWhen } from '@metafox/utils';
import { takeEvery, put, take } from 'redux-saga/effects';

export function* presentAppMenu({
  payload: { appName, menuName, dependName },
  meta
}: LocalAction<
  { appName: string; menuName: string; dependName?: string },
  {
    setMenu: (data: any) => void;
  }
>) {
  const menu: MenuShape = yield* getAppMenu(appName, menuName);

  const session = yield* getSession();
  const { acl, setting, getPageParams } = yield* getGlobalContext();
  const { profile_id, profile_type, identity: subjectId } = getPageParams();

  const subject = yield* getItem(subjectId);

  let data: Record<string, any>;

  if (dependName) {
    const dependActionName = `${dependName}/context`;

    yield put({
      type: dependName,
      payload: { dependActionName }
    });
    const action = yield take(dependActionName);

    data = action.payload;
  }

  if (!data)
    data = {
      acl,
      setting,
      session,
      profile_id,
      profile_type,
      subject
    };

  if (!data.session) data.session = session;

  if (!data.subject) data.subject = subject;

  if (!menu?.items) return;

  const items = filterShowWhen(menu.items, data);

  if (meta?.setMenu) meta.setMenu(items);
}

const sagaEffect = [takeEvery('presentAppMenu', presentAppMenu)];

export default sagaEffect;
