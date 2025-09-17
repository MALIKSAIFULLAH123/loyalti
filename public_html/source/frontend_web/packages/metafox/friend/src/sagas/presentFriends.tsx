/**
 * @type: saga
 * name: friend.saga.presentFriends
 */
import { getGlobalContext, getItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* presentFriends(action: {
  type: string;
  payload: {
    identity: string;
  };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  let user_id;

  if ('user' === item.resource_name) {
    user_id = item.id;
  } else if (item.user?.id) {
    user_id = item.user.id;
  } else {
    const user = yield* getItem(item.user);
    user_id = user?.id;
  }

  const { dialogBackend } = yield* getGlobalContext();

  dialogBackend.present({
    component: 'friend.dialog.presentFriends',
    props: { user_id }
  });
}

const sagaEffect = [takeEvery('friend/presentFriends', presentFriends)];

export default sagaEffect;
