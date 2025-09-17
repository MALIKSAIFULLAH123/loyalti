/**
 * @type: service
 * name: useResourceMenu
 */
import { GlobalState } from '@metafox/framework/Manager';
import { get } from 'lodash';
import { useSelector } from 'react-redux';
import { MenuShape } from '../types';

export default function useResourceMenu(
  appName: string,
  resourceName: string,
  menuName: string
): MenuShape | undefined {
  return useSelector<GlobalState>(
    state =>
      get(state, `_resourceMenus.${appName}.${resourceName}.${menuName}`),
    () => true
  ) as any;
}
