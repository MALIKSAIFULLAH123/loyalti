import { HandleAction } from '@metafox/framework';

export default function feedItemActions(handleAction: HandleAction) {
  return {
    viewMoreComments: (payload, meta) =>
      handleAction('comment/viewMoreComments', payload, meta)
  };
}
