import { HandleAction } from '@metafox/framework';

export default function PollItemActions(dispatch: HandleAction) {
  return {
    deletePoll: () => dispatch('deleteItem')
  };
}
