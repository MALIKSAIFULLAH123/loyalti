import { HandleAction } from '@metafox/framework';
import { SeventItemActions } from '../types';

export default function seventItemActions(
  dispatch: HandleAction
): SeventItemActions {
  return {
    deleteItem: () => dispatch('deleteItem'),
    approveItem: () => dispatch('approveItem'),
    addTicketItem: () => dispatch('addTicketItem'),
    paymentItem: () => dispatch('sevent/paymentItem')
  };
}
