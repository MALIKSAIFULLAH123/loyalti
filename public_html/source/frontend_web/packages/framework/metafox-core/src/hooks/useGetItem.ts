/**
 * @type: service
 * name: useGetItem
 */

import { GlobalState } from '@metafox/framework/Manager';
import { useSelector } from 'react-redux';
import { getItemSelector } from '../selectors';

export default function useGetItem<T = any>(identity: string) {
  return useSelector<GlobalState, T>(state => getItemSelector(state, identity));
}
