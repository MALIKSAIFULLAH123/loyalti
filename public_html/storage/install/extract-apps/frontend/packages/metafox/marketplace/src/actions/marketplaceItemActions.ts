import { HandleAction } from '@metafox/framework';

export default function marketplaceItemActions(dispatch: HandleAction) {
  return {
    deleteList: () => dispatch('deleteItem'),
    approveList: () => dispatch('approveItem'),
    paymentItem: () => dispatch('marketplace/paymentItem')
  };
}
