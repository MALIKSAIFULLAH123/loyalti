import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { useSelector } from 'react-redux';
import { AppState } from '../types';

export const getAnnouncements = (state: GlobalState) =>
  state.announcement.loaded;

export const getStatistic = (state: GlobalState) =>
  get(state, 'announcement.statistic');

export function useAnnouncements() {
  const loaded = useSelector<GlobalState, AppState['loaded']>(state =>
    getAnnouncements(state)
  );

  const statistic = useSelector<GlobalState, AppState['statistic']>(state =>
    getStatistic(state)
  );

  return { loaded, statistic };
}
