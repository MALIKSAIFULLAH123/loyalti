import { HandleAction } from '@metafox/framework';
import { SeventItemActions } from '../types';

export default function ticketItemActions(
  dispatch: HandleAction
): SeventItemActions {
  return {
    deleteItem: () => dispatch('deleteItem')
  };
}
