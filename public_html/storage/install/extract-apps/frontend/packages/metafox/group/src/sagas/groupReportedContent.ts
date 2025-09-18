/**
 * @type: saga
 * name: saga.group.reportedContent
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { compactUrl } from '@metafox/utils';
import { APP_REPORT } from '@metafox/report';

function* keepPost(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { apiClient } = yield* getGlobalContext();

  const dataSource = yield* getItemActionConfig(
    { module_name: APP_REPORT, resource_name: 'report_owner' },
    'keepPost'
  );

  const { apiUrl, apiMethod, apiParams } = dataSource;

  try {
    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id }),
      params: apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* removePost(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity }
  } = action;

  const { apiClient } = yield* getGlobalContext();
  const { apiUrl, apiMethod, apiParams } = yield* getItemActionConfig(
    { module_name: APP_REPORT, resource_name: 'report_owner' },
    'removePost'
  );

  try {
    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: compactUrl(apiUrl, { id }),
      params: apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* getListReport(action: {
  type: string;
  payload: { identity: string };
}) {
  const { dialogBackend } = yield* getGlobalContext();
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return null;

  yield dialogBackend.present({
    component: 'group.dialog.listReportDialog',
    props: { id: item.id }
  });
}

const sagas = [
  takeEvery('group/keepReportedPost', keepPost),
  takeEvery('group/removeReportedPost', removePost),
  takeEvery('group/getListReport', getListReport)
];

export default sagas;
