/**
 * @type: service
 * name: useSessionSummary
 */
import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';
import { SessionState } from '../types';

const getSession = (state: GlobalState): SessionState => state.session;

const getSessionSelector = createSelector(getSession, session => session);

export default function useSessionSummary() {
  const session = useSelector(getSessionSelector);

  return session;
}
