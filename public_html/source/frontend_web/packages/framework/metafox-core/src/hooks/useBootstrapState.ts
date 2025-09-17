import { GlobalState } from '@metafox/framework/Manager';
import { useSelector } from 'react-redux';

export default function useBootstrapState() {
  return useSelector<GlobalState, GlobalState['bootstrap']>(
    state => state.bootstrap
  );
}
