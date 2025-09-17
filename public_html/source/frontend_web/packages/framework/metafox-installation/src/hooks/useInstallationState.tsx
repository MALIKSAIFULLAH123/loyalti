import { useSelector } from 'react-redux';
import { GlobalState } from '@metafox/framework';

export default function useInstallationState() {
  return useSelector((state: GlobalState) => state.installation);
}
