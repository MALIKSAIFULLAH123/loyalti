/**
 * @type: service
 * name: useGetActionHeader
 */

import { GlobalState } from '@metafox/framework';
import { createSelector } from 'reselect';
import { get } from 'lodash';
import { useSelector } from 'react-redux';

const getHeaderAction = (state: GlobalState, resourcePath: string) =>
  (resourcePath ? get(state, `_resourceMenus.${resourcePath}.items`) : null);

const getProfileHeaderActions = createSelector(getHeaderAction, data => data);

export default function useGetActionHeader<T = any>(resourcePath: string) {
  return useSelector<GlobalState, T>(state =>
    getProfileHeaderActions(state, resourcePath)
  );
}
