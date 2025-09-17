/**
 * @type: saga
 * name: core.showReportDialogSaga
 */
import { getGlobalContext, ItemLocalAction } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* showReportDialogSaga({ payload }: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();
  yield dialogBackend.present({ component: 'dialog.core.report', props: {} });
}

const sagaEffect = takeEvery('showReportDialog', showReportDialogSaga);

export default sagaEffect;
