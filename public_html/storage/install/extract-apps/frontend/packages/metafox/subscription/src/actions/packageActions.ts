import { HandleAction } from '@metafox/framework';

export default function packageActions(dispatch: HandleAction) {
  return {
    paymentItem: () => dispatch('subscription/paymentPackage')
  };
}
