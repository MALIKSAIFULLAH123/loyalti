/**
 * @type: saga
 * name: user.declineUser
 */
import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* declineUser(action: ItemLocalAction) {
  const {
    payload: { identity },
    meta: { onFinal }
  } = action;

  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend, normalization } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(item, 'deniedItem');

  if (!dataSource?.apiUrl) return;

  const ok = yield* handleActionConfirm(dataSource);

  if (!ok) {
    onFinal && onFinal();

    return false;
  }

  try {
    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        pageParams: { id: item.id }
      }
    });

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    onFinal && onFinal();
  } catch (err) {
    onFinal && onFinal();
    yield* handleActionError(err);
  }
}

const sagas = [takeEvery('user/decline', declineUser)];

export default sagas;
