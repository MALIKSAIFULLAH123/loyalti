import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { createSelector } from 'reselect';

const getStatus = (state: GlobalState) => state.core.status;

export const getStatusSelector = createSelector(getStatus, item => item);

const getTaggedFriendsPhoto = (state: GlobalState, item: any) => {
  if (!item) return null;

  const result = item?.tagged_friends
    ? item.tagged_friends.map((x: any) => get(state, x)).filter(Boolean)
    : undefined;

  if (result?.length && result[0]?.resource_name === 'photo_tag') {
    return result.map((x: any) => get(state, x?.user));
  }

  return result;
};

export const getTaggedFriendsPhotoSelector = createSelector(
  getTaggedFriendsPhoto,
  item => item
);

const getUserTagPhoto = (state: GlobalState, item: any) => {
  return item ? item.map((x: any) => get(state, x)).filter(Boolean) : undefined;
};

export const getUserTagPhotoSelector = createSelector(
  getUserTagPhoto,
  item => item
);
