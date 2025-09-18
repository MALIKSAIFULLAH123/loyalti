/**
 * @type: saga
 * name: invite.saga.updatedItem
 */

import { getGlobalContext, LocalAction } from '@metafox/framework';
import { isEmpty } from 'lodash';
import { takeEvery } from 'redux-saga/effects';

function* redirectManage({
  payload
}: LocalAction<{ communities?: any; duplicates?: any }>) {
  const { navigate, dialogBackend } = yield* getGlobalContext();

  if (isEmpty(payload?.communities) && isEmpty(payload?.duplicates))
    navigate('/invite/manage');
  else {
    yield dialogBackend.present({
      component: 'invite.dialog.listInviteResult',
      props: payload
    });
  }
}

const sagas = [takeEvery('invite/redirectManage', redirectManage)];

export default sagas;
