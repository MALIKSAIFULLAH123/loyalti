import { createSelector } from 'reselect';
import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';

const getQuizResult = (state: GlobalState, identity: string) => {
  return identity ? get(state, identity) : undefined;
};

export const getQuizResultSelector = createSelector(
  getQuizResult,
  item => item
);
