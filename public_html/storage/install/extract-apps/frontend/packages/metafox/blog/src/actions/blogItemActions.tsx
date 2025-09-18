import { HandleAction } from '@metafox/framework';
import { BlogItemActions } from '../types';

export default function blogItemActions(
  dispatch: HandleAction
): BlogItemActions {
  return {
    deleteItem: () => dispatch('deleteItem'),
    approveItem: () => dispatch('approveItem')
  };
}
