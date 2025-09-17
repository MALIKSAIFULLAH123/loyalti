import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';
import { get } from 'lodash';

const getFeed = (
  state: GlobalState,
  user_identity: string,
  feed_identity: string
) => {
  if (!state.feed.entities.feed) return undefined;

  const resultArray = [];
  const feedItemArray = Object.values(state.feed.entities.feed);

  feedItemArray.forEach(item => {
    if (
      (item.user === user_identity || item.parent_user === user_identity) &&
      item._identity !== feed_identity
    )
      resultArray.push(item._identity);
  });

  feedItemArray.forEach(item => {
    if (
      resultArray.includes(item.embed_object) &&
      item._identity !== feed_identity
    )
      resultArray.push(item._identity);
  });

  return resultArray;
};

export const getFeedWithUserIdentitySelector = createSelector(
  getFeed,
  item => item
);

const getTaggedFriends = (state: GlobalState, item: any) => {
  return item?.tagged_friends
    ? item.tagged_friends.map((x: string) => get(state, x)).filter(Boolean)
    : undefined;
};

export const getTaggedFriendsSelector = createSelector(
  getTaggedFriends,
  item => item
);
