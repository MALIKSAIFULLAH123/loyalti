import { AppResourceAction, GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';
import { useSelector } from 'react-redux';

export default function useResourceActions(
  appName: string,
  resourceName: string
): Record<string, AppResourceAction> {
  return useSelector<GlobalState>(
    state => get(state, `_actions.${appName}.${resourceName}`),
    () => true
  ) as any;
}
