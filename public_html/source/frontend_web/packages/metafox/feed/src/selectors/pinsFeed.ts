import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';

const getFeed = (state: GlobalState, profileId: number) => {
  if (!state.feed.entities.feed) return undefined;

  const data = Object.values(state.feed.entities.feed);
  const total = data.filter(
    obj => obj.pins && obj.pins.length && obj.pins.includes(profileId)
  ).length;

  return total;
};

export const getTotalPinsFeed = createSelector(getFeed, data => data);
