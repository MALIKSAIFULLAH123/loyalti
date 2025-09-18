/**
 * @type: service
 * name: useReactions
 */

import { GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';

const getReactions = (state: GlobalState) =>
  (state.preaction.data.reactions || []).filter(item => item.is_active);

const getReactionSelector = createSelector(getReactions, data => data);

export default function useReactions() {
  return useSelector(getReactionSelector);
}
