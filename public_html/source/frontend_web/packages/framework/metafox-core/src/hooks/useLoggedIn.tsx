/**
 * @type: service
 * name: useLoggedIn
 */
import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';

const getLoggedIn = (state: GlobalState): boolean => state.session.loggedIn;

const getLoggedInSelector = createSelector(getLoggedIn, data => data);

export default function useLoggedIn() {
  return useSelector(getLoggedInSelector);
}
