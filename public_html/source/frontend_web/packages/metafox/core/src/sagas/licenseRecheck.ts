/**
 * @type: saga
 * name: licenseRecheck
 */

import {
  ACP_LICENSE_RECHECK,
  LocalAction,
  getGlobalContext,
  handleActionFeedback,
  handleActionError
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { getAdminSiteStatus } from '@metafox/admincp/actions';

function* handleAction({
  payload,
  meta
}: LocalAction<
  { apiMethod: string; apiUrl: string },
  { setLoading: (x: any) => void; hideAlert: () => void }
>) {
  const { apiMethod, apiUrl } = payload;
  const { apiClient } = yield* getGlobalContext();
  meta?.setLoading && meta?.setLoading(true);

  if (apiUrl) {
    try {
      const response = yield apiClient.request({
        method: apiMethod || 'GET',
        url: apiUrl
      });
      const result = response.data.data;

      if (result) {
        if (result?.valid === 1) {
          meta?.hideAlert && meta?.hideAlert();
          yield put(getAdminSiteStatus(true));
        }
      }

      yield* handleActionFeedback(response);
    } catch (error) {
      yield* handleActionError(error);
    }
  }

  meta?.setLoading && meta?.setLoading(false);
}

const sagas = [takeEvery(ACP_LICENSE_RECHECK, handleAction)];

export default sagas;
