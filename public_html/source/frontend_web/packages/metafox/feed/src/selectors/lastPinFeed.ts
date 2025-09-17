import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';
import { get } from 'lodash';

const getLastPin = (
  state: GlobalState,
  profileId: number,
  pagingId: string
) => {
  if (!state.feed.entities.feed) return undefined;

  const ids = get(state, `pagination.${pagingId}.ids`);

  if (ids?.length < 1) return 0;

  const lastItem = ids.find(id => {
    const obj = get(state, id);

    return obj?.pins?.includes(profileId);
  });

  return get(state, lastItem);
};

export const getLastPinFeed = createSelector(getLastPin, data => data);
