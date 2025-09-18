import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';

export const getReactionItem = (state: GlobalState, id: string) =>
  state.preaction.entities.preaction[id];

export const getReactionItemSelector = createSelector(
  getReactionItem,
  item => item
);
