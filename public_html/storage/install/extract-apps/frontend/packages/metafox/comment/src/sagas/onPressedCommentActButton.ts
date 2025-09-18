/**
 * @type: saga
 * name: comment.onPressedCommentActButton
 */

import { ItemLocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* openReplyComposer({ type, payload, meta }) {
  if (meta?.setLocalState) {
    meta.setLocalState(prev => ({
      ...prev,
      commentOpened: true,
      commentFocused: (prev.commentFocused || 1) + 1,
      replyUser: payload?.replyUser
    }));
  }

  yield;
}

export function* onPressedCommentActButton({ meta }: ItemLocalAction) {
  if (meta?.setLocalState) {
    meta.setLocalState(prev => ({
      ...prev,
      commentOpened: true,
      commentFocused: (prev.commentFocused || 1) + 1
    }));
  }

  yield;
}

const sagas = [
  takeEvery('onPressedCommentActButton', onPressedCommentActButton),
  takeEvery('comment/openReplyComposer', openReplyComposer)
];

export default sagas;
