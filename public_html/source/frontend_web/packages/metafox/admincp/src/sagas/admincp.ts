/**
 * @type: saga
 * name: admincp
 * bundle: admincp
 */

import {
  getGlobalContext,
  handleActionError,
  LocalAction,
  handleActionFeedback,
  GridMassAction,
  handleActionConfirm,
  AppResourceAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';
import { ADMINCP_SITE_STATUS_GET } from '../constants';
import {
  putAdminSiteStatus,
  setAdminSiteLoading,
  startGetAdminSiteStatus
} from '../actions';

function* showCacheDialog() {
  const { dialogBackend } = yield* getGlobalContext();

  dialogBackend.present({
    component: 'core.dialog.RemoteForm',
    dialogId: 'admincp_clear_cache_dialog',
    props: {
      dataSource: {
        apiUrl: 'admincp/core/form/core.admin.destroy_cache'
      }
    }
  });
}

function* getSiteStatus({
  payload: { reload }
}: LocalAction<Record<string, any>>) {
  try {
    yield put(startGetAdminSiteStatus());
    const { apiClient } = yield* getGlobalContext();

    const dataSource = {
      apiUrl: reload
        ? 'admincp/dashboard/site-status?reload=true'
        : 'admincp/dashboard/site-status'
    };

    const response = yield apiClient.request({
      url: dataSource.apiUrl,
      method: 'GET'
    });
    const data = response.data?.data;

    if (data) {
      yield put(putAdminSiteStatus(data));
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* batchItem({
  payload: { id, dataSource, action, reload, confirm, showProgress },
  meta: { apiRef }
}: GridMassAction & { payload: { confirm?: any } }) {
  // const { dialogBackend } = yield* getGlobalContext();
  try {
    const { apiClient, compactUrl } = yield* getGlobalContext();

    dataSource = dataSource ?? apiRef.current.config.actions[action];

    const ok = yield handleActionConfirm({ confirm } as AppResourceAction);

    if (!ok) return;

    if (showProgress) {
      yield put(setAdminSiteLoading(true));
    }

    const response = yield apiClient.request({
      url: compactUrl(dataSource.apiUrl, { id }),
      data: dataSource.apiParams,
      method: dataSource.apiMethod || 'post'
    });

    if (showProgress) {
      yield put(setAdminSiteLoading(false));
    }

    yield* handleActionFeedback(response);

    if (reload) {
      apiRef.current.refresh();
    }
  } catch (error) {
    if (showProgress) {
      yield put(setAdminSiteLoading(false));
    }

    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('@admin/showCacheDialog', showCacheDialog),
  takeEvery('@admin/batchItem', batchItem),
  takeEvery(ADMINCP_SITE_STATUS_GET, getSiteStatus)
];

export default sagas;
