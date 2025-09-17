import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';

const getRequestFriend = (state: GlobalState, identity: string) => {
  if (!state.friend.entities.friend_request) return undefined;

  const resultArray = Object.values(state.friend.entities.friend_request);

  return resultArray.find(item => item.user === identity);
};

export const getRequestFriendSelector = createSelector(
  getRequestFriend,
  item => item
);
