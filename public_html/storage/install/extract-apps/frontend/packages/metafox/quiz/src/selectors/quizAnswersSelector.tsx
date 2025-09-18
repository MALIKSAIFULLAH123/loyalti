import { createSelector } from 'reselect';
import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';

const getQuizAnswers = (state: GlobalState, identities: string[]) => {
  return identities ? identities?.map(x => get(state, x)).filter(Boolean) : [];
};

export const getQuizAnswersSelector = createSelector(
  getQuizAnswers,
  item => item
);
