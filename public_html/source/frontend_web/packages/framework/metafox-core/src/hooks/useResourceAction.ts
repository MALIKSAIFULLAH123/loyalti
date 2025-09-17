import { AppResourceAction, GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';
import { useSelector } from 'react-redux';

export default function useResourceAction(
  appName: string,
  resourceName: string,
  actionName: string
): AppResourceAction | undefined {
  return useSelector<GlobalState, AppResourceAction>(state =>
    get(state, `_actions.${appName}.${resourceName}.${actionName}`)
  ) as any;
}
