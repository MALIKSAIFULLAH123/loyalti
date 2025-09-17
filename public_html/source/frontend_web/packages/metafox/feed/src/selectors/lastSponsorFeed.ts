import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';
import { get } from 'lodash';

const getLastSponsor = (state: GlobalState, pagingId: string) => {
  if (!state.feed.entities.feed) return undefined;

  const ids = get(state, `pagination.${pagingId}.ids`);
  const lastItem = ids.find(id => {
    const obj = get(state, id);

    return obj?.is_sponsor;
  });

  return get(state, lastItem);
};

export const getLastSponsorFeed = createSelector(getLastSponsor, data => data);
