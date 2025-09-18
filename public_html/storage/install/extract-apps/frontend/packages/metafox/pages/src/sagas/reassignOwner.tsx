/**
 * @type: saga
 * name: saga.pages.reassignOwner
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  ItemLocalAction
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* reassignOwner(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { dialogBackend } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'reassignOwner');

  if (!config?.apiUrl) return;

  const ok = yield handleActionConfirm(config);

  if (!ok) return;

  yield dialogBackend.present({
    component: 'dialog.pages.reassignOwner',
    props: {}
  });
}

const sagas = [takeLatest('pages/reassignOwner', reassignOwner)];

export default sagas;
