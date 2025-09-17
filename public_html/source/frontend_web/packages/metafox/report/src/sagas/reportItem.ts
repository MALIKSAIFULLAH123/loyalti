/**
 * @type: saga
 * name: saga.reportItem
 */

import { getGlobalContext, getItem, ItemLocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* showReportDialog(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'report.dialog.addReport',
    props: {
      item_id: item.id,
      item_type: item.resource_name
    }
  });
}

const sagas = [takeEvery('reportItem', showReportDialog)];

export default sagas;
