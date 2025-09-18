/**
 * @type: saga
 * name: livestreaming.saga.updateStatus
 */

import { ItemLocalAction, patchEntity } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* updateStatus(action: ItemLocalAction) {
  const { identity } = action.payload;

  try {
    yield* patchEntity(identity, { is_streaming: false, _live_watching: true });
  } catch (error) {}
}

function* removeLiveWatching(action: ItemLocalAction) {
  const { identity } = action.payload;

  try {
    yield* patchEntity(identity, { _live_watching: false });
  } catch (error) {}
}

const sagas = [
  takeLatest('livestreaming/updateStatusOffline', updateStatus),
  takeLatest('livestreaming/removeLiveWatching', removeLiveWatching)
];

export default sagas;
