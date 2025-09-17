/**
 * @type: service
 * name: useSession
 */
import { getItemSelector, GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';
import { SessionState } from '../types';

const getSession = (state: GlobalState): SessionState => state.session;

const getSessionSelector = createSelector(getSession, session => session);

export default function useSession() {
  const session = useSelector(getSessionSelector);

  const user = useSelector((state: GlobalState) =>
    getItemSelector(state, `user.entities.user.${session.user?.id}`)
  );

  if (!user) return session;

  return { ...session, user: { ...session.user, ...user } };
}
