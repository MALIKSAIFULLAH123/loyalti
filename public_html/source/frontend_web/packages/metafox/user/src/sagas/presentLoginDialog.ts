/**
 * @type: saga
 * name: user.saga.showDialogLogin
 */

import { getGlobalContext, handleActionError } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* showDialogLogin({ meta }: { meta: { onSuccess: () => void } }) {
  const { dialogBackend } = yield* getGlobalContext();
  const { onSuccess } = meta || {};

  try {
    yield dialogBackend.present({
      component: 'user.dialog.LoginDialog'
    });
  } catch (error) {
    yield* handleActionError(error);
  }

  if (onSuccess) onSuccess();
}

const sagas = [takeEvery('user/showDialogLogin', showDialogLogin)];

export default sagas;
