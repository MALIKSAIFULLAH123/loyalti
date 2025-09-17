/**
 * @type: service
 * name: useAppMenu
 */
import { GlobalState } from '@metafox/framework/Manager';
import { useSelector } from 'react-redux';
import { getAppMenuSelector } from '../selectors';
import { MenuShape } from '../types';

export default function useAppMenu(
  appName: string,
  menuName: string
): MenuShape | undefined {
  return useSelector<GlobalState, MenuShape>(state =>
    getAppMenuSelector(state, appName, menuName)
  );
}
