/**
 * @type: saga
 * name: saga.reportToOwner
 */

import { getGlobalContext, getItem, ItemLocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* showReportOwnerDialog(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'report.dialog.addReportOwner',
    props: {
      item_id: item.id,
      item_type: item.module_name
    }
  });
}

const sagas = [takeEvery('reportToOwner', showReportOwnerDialog)];

export default sagas;
