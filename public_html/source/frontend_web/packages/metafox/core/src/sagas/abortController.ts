/**
 * @type: saga
 * name: abortController
 */
/* eslint-disable require-yield */
import {
  ABORT_CONTROL_CANCEL,
  ABORT_CONTROL_START,
  LocalAction
} from '@metafox/framework';
import { CancelTokenSource } from 'axios';
import { takeEvery } from 'redux-saga/effects';

const abortDict: Record<string, CancelTokenSource[]> = {};

function* abortControlCancel({
  payload: { abortId }
}: LocalAction<{ abortId: string }>) {
  const sources = abortDict[abortId];

  if (sources) {
    sources.forEach(source => {
      source.cancel();
    });
    delete abortDict[abortId];
  }
}

// eslint-disable-next-line require-yield
function* abortControlStart({
  payload: { abortId, source }
}: LocalAction<{ abortId: string; source: CancelTokenSource }>) {
  if (!abortDict[abortId]) abortDict[abortId] = [];

  abortDict[abortId].push(source);
}

const sagas = [
  takeEvery(ABORT_CONTROL_CANCEL, abortControlCancel),
  takeEvery(ABORT_CONTROL_START, abortControlStart)
];

export default sagas;
