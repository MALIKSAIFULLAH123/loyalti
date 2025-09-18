/**
 * @type: saga
 * name: comment.onPressedReplyActButton
 */

import { ItemLocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* onPressedReplyActButton({ meta }: ItemLocalAction) {
  if (meta?.setLocalState)
    meta.setLocalState(prev => ({
      ...prev,
      commentOpened: true,
      commentFocused: (prev.commentFocused || 1) + 1
    }));

  yield;
}

const sagas = [takeEvery('onPressedReplyActButton', onPressedReplyActButton)];

export default sagas;
