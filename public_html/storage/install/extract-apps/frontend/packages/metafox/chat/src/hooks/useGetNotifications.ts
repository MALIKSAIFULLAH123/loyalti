import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { AppState } from '../types';
import { getNotifications } from '../selectors';

export default function useGetNotifications() {
  return useSelector<GlobalState, AppState['notifications']>(state =>
    getNotifications(state)
  );
}
