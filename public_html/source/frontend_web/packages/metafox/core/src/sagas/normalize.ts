/**
 * @type: saga
 * name: normalize
 */

import { fulfillEntity, getGlobalContext } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* normalizeSaga({ payload }: any) {
  const { normalization } = yield* getGlobalContext();

  if (!payload) return;

  const result = normalization.normalize(payload);

  yield* fulfillEntity(result.data);
}

const sagas = [takeEvery('@normalize', normalizeSaga)];

export default sagas;
