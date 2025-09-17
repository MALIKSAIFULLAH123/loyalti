/**
 * @type: service
 * name: useGetItems
 */

import { GlobalState } from '@metafox/framework/Manager';
import { useSelector } from 'react-redux';
import { getItemsSelector } from '../selectors';

export default function useGetItem<T = any>(ids: string[]) {
  return useSelector<GlobalState, T[]>(state => getItemsSelector(state, ids));
}
