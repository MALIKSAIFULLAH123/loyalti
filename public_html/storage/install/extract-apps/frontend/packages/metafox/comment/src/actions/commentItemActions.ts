import { HandleAction } from '@metafox/framework';
import { CommentItemActions } from '../types';

export default function commentItemActions(
  dispatch: HandleAction
): CommentItemActions {
  return {
    openReplyComposer: data => dispatch('comment/openReplyComposer', data),
    viewMoreReplies: (payload, meta) =>
      dispatch('comment/viewMoreReplies', payload, meta),
    viewMoreComments: (payload, meta) =>
      dispatch('comment/viewMoreComments', payload, meta),
    removePreviewLink: () => dispatch('comment/removePreviewLink'),
    deleteComment: () => dispatch('deleteComment')
  };
}
