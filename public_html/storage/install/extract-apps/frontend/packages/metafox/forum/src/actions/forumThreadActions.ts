import { HandleAction } from '@metafox/framework';
import { ForumThreadItemActions } from '../types';

export default function forumThreadActions(
  dispatch: HandleAction
): ForumThreadItemActions {
  return {
    deleteItem: () => dispatch('deleteItem'),
    approveItem: () => dispatch('approveItem')
  };
}
