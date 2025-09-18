/**
 * @type: saga
 * name: comment.toggleItemComments
 */

import { ItemLocalAction } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

export function* toggleItemComments({ meta }: ItemLocalAction) {
  if (meta.setLocalState)
    meta.setLocalState(prev => ({
      ...prev,
      commentOpened: !prev.commentOpened
    }));

  yield;
}

const sagas = [takeLatest('toggleItemComments', toggleItemComments)];

export default sagas;
