/**
 * @type: saga
 * name: sevent.saga.massEmailEvent
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';
function* massEmail(
  action: ItemLocalAction & {
    payload: { identity: string };
  }
) {
  const {
    payload: { identity }
  } = action;

  const { dialogBackend, compactUrl } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource: {
          apiUrl: compactUrl('core/form/sevent.mass_email/:id', item)
        }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  
  takeLatest('sevent/massEmailEvent', massEmail)
];

export default sagas;
