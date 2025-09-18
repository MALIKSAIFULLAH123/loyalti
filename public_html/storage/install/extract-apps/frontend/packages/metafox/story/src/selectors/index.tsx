import { GlobalState, PagingState } from '@metafox/framework';
import { get } from 'lodash';
import { createSelector } from 'reselect';

export const getMutedStatus = (state: GlobalState) =>
  get(state, 'story.storyStatus');

function getPagingArchiveNoEnded(
  state: GlobalState,
  prefixPagingId: string
): PagingState[] {
  const pagingId = [];

  Object.keys(state.pagination).forEach(pageId => {
    if (pageId.startsWith(prefixPagingId)) {
      if (state.pagination[pageId]?.ended === false) {
        pagingId.push(pageId);
      }
    }
  });

  return pagingId;
}

export const getPagingArchiveNoEndedSelector = createSelector(
  getPagingArchiveNoEnded,
  data => data
);
