/**
 * @type: saga
 * name: group.saga.presentMutualFriends
 */
import { getGlobalContext, getItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* presentMutualFriends(action: {
  type: string;
  payload: { identity: string };
}) {
  const { dialogBackend } = yield* getGlobalContext();
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  let user_id;

  if ('user' === item.resource_name) {
    user_id = item.id;
  } else if ('group_invite' === item.resource_name && item?.owner) {
    const user = yield* getItem(item?.owner);
    user_id = user?.id;
  } else if (item.user?.id) {
    user_id = item.user.id;
  } else {
    const user = yield* getItem(item.user);
    user_id = user?.id;
  }

  yield dialogBackend.present({
    component: 'friend.dialog.presentMutualFriends',
    props: { user_id }
  });
}

const sagaEffect = [
  takeEvery('group/presentMutualFriends', presentMutualFriends)
];

export default sagaEffect;
