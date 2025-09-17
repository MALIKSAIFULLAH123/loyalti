import { useSelector } from 'react-redux';
import { GlobalState } from '@metafox/framework';

export default function useUpgradeState() {
  return useSelector((state: GlobalState) => state.app.upgrade);
}
