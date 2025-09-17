/**
 * @type: saga
 * name: saga.core.closeDialog
 */
import { getGlobalContext } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* closeDialog(action: any) {
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.dismiss();
}

const sagas = [takeEvery('closeDialog', closeDialog)];

export default sagas;
