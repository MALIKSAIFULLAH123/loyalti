import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { getFriends, getSpotlight } from '../selectors';
import { AppState } from '../types';

export default function useSpotlight() {
  return useSelector<GlobalState, AppState['spotlight']>(state =>
    getSpotlight(state)
  );
}

export function useGetSpotlightUserInit() {
  const friends = useSelector<GlobalState, AppState['friends']>(state =>
    getFriends(state)
  );

  return Object.values(friends) ? Object.values(friends)?.slice(0, 4) : [];
}
