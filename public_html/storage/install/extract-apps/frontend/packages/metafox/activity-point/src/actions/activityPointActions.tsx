import { HandleAction } from '@metafox/framework';

export default function activityPointActions(dispatch: HandleAction) {
  return {
    purchase: () => dispatch('activityPoint/purchase')
  };
}
