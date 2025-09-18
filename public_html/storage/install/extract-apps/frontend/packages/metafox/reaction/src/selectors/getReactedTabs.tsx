import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';

const getTabs = (state: GlobalState, identity: string) =>
  state.preaction.reactedTabs[identity];

export const getTabsSelector = createSelector(getTabs, tabs => tabs);
