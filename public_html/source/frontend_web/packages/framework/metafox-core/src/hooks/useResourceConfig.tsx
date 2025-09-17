import { AppResource, GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';
import { useSelector } from 'react-redux';

export default function useResourceConfig(
  appName: string,
  resourceName: string
): AppResource | undefined {
  return useSelector<GlobalState, AppResource>(state =>
    get(state, `_resourceMenus.${appName}.${resourceName}`)
  ) as any;
}
