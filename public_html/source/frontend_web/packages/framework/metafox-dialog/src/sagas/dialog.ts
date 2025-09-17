/**
 * @type: saga
 * name: metafox-dialog
 */
import { takeEvery } from 'redux-saga/effects';
import { getGlobalContext, LocalAction } from '@metafox/framework';
import { AlertParams } from '../types';

function* presentDialog<T extends object = object>(
  action: LocalAction<{
    component: string;
    payload: T;
  }>
) {
  const { dialogBackend } = yield* getGlobalContext();

  return yield dialogBackend.present(action.payload);
}

function* presentAlert(action: LocalAction<AlertParams>) {
  const { dialogBackend } = yield* getGlobalContext();

  return yield dialogBackend.alert(action.payload);
}

const sagas = [
  takeEvery('dialog/present', presentDialog),
  takeEvery('alert', presentAlert)
];

export default sagas;
