import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';
import { AppState } from '../types';

const getBgStatus = (state: GlobalState): AppState['collections'] =>
  state?.['background-status']?.collections;

export const getBgStatusSelector = createSelector(getBgStatus, data => data);
