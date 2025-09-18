import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { AppState } from '../types';
import { getStatusTourguide } from '../selectors';

export default function useStatusCreate() {
  return useSelector<GlobalState, AppState['statusTourguide']>(state =>
    getStatusTourguide(state)
  );
}
