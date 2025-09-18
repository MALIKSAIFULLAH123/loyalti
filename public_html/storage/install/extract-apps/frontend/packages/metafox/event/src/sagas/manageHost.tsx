/**
 * @type: saga
 * name: event/manageHostInvite
 */

import { getGlobalContext, getItem, ItemLocalAction } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* viewAllHost({
  payload: { identity, defaultTab }
}: ItemLocalAction & { payload: { defaultTab: string } }) {
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'event.dialog.viewHostDialog',
    props: {
      item,
      defaultTab
    }
  });
}

const sagas = [takeLatest('event/viewAllHost', viewAllHost)];

export default sagas;
