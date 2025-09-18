import { useGlobal, GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';
import { createSelector } from 'reselect';

export default function useReactionChat() {
  const { getSetting } = useGlobal();
  const reactionList = getSetting('preaction.reaction_list', []).filter(
    item => item.is_active
  );
  const inactiveReactionList = getSetting(
    'preaction.inactive_reaction_list',
    []
  );

  const data = [...reactionList, ...inactiveReactionList];

  return data?.length ? data : [];
}

// TODO: Hotfix issue import - Remove when update next version
const getReactions = (state: GlobalState) =>
  (state.preaction.data.reactions || []).filter(item => item.is_active);

const getReactionSelector = createSelector(getReactions, data => data);

export function useReactionTemporary() {
  return useSelector(getReactionSelector);
}
