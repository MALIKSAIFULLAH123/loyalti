/**
 * @type: saga
 * name: tourguide
 */

import {
  fulfillEntity,
  getGlobalContext,
  getItem,
  getResourceConfig,
  getSession,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  IS_ADMINCP
} from '@metafox/framework';
import { takeEvery, takeLatest, put, take } from 'redux-saga/effects';
import { APP_NAME, RESOURCE_TOURGUIDE } from '../constants';
import { compactData, compactUrl } from '@metafox/utils';
import { isEmpty } from 'lodash';
import { ActionTypeTour, StatusTourGuide, TourGuideStep } from '../types';
let params;

export function* updateParams({ payload }) {
  params = payload;
  yield;
}

export function* getBootstrapParam() {
  const paramsAware = yield take('@PageParamAware/DONE');

  if (paramsAware?.payload?.identity && paramsAware?.payload?.tab) {
    const item = yield* getItem(paramsAware?.payload?.identity);

    if (!item) {
      yield take('createPage/Done');

      return params;
    }
  }

  return paramsAware?.payload;
}

export function* viewItem(action) {
  const { apiClient, normalization } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const { data } = action?.payload || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'viewItem'
  );

  if (!config.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, { ...data })
    });

    const result = response.data?.data;

    if (result) {
      const _result = normalization.normalize(result);
      yield* fulfillEntity(_result.data);
    }

    onSuccess && onSuccess(result);

    return result;
  } catch (error) {
    yield handleActionError(error);
  }
}

export function* createTourSuccess(data) {
  const responseData = data.payload;

  yield put({
    type: 'tourguide/reducer/updateStatus',
    payload: {
      tourguide_id: responseData?.id,
      status: StatusTourGuide.Create,
      createStep: TourGuideStep.SelectElement
    }
  });
}

export function* createTour(action) {
  const { onSuccess, onCancel } = action?.meta || {};
  const { typeAction = ActionTypeTour.create } = action?.payload || {};

  const { dialogBackend, getPageParams } = yield* getGlobalContext();
  const pageParams: any = getPageParams();

  const dataSource = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'addItem'
  );

  if (!dataSource) return;

  try {
    yield put({
      type: 'tourguide/reducer/updateStatus',
      payload: {
        status: StatusTourGuide.Create,
        createStep: TourGuideStep.Tour
      }
    });

    yield dialogBackend
      .present({
        component: 'core.dialog.RemoteForm',
        props: {
          dataSource,
          keepPaginationData: true,
          initialValues: {
            url: window.location.href,
            page_name: pageParams?.pageMetaName || pageParams?.pageName
          },
          successAction: 'tourguide/createTour/success'
        }
      })
      .then(data => {
        if (data?.id) {
          onSuccess && onSuccess({ ...data, typeAction });
        } else {
          onCancel && onCancel();
        }
      })
      .catch(err => {
        onCancel && onCancel();
      });
  } catch (error) {
    onCancel && onCancel();
    yield* handleActionError(error);
  }
}

export function* getActions(action) {
  const { apiClient } = yield* getGlobalContext();
  const { onDone, fire } = action?.meta || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'getActions'
  );
  const pageParams = yield* getBootstrapParam();

  if (IS_ADMINCP || !config?.apiUrl || isEmpty(pageParams?.pageMetaName))
    return;

  fire({
    type: 'setUpdate',
    payload: {
      pageParams
    }
  });

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl,
      params: compactData(config.apiParams, {
        page_name: pageParams?.pageMetaName
      })
    });

    const data = response.data?.data;

    if (!data) return;

    onDone && onDone({ data });
  } catch (error) {
    onDone && onDone({ data: [] });
    yield;
  }
}

export function* markAsActive(action) {
  const { apiClient } = yield* getGlobalContext();
  const { id } = action?.payload || {};
  const { onSuccess } = action?.meta || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'markAsActive'
  );

  if (!config.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, { id })
    });

    onSuccess && onSuccess();
    yield* handleActionFeedback(response);

    yield put({ type: 'navigate/reload' });
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* hideTour(action) {
  const { apiClient } = yield* getGlobalContext();
  const { loggedIn } = yield* getSession();
  const { onSuccess } = action?.meta || {};
  const { data, hasConfirm = true } = action?.payload || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'hideItem'
  );

  try {
    onSuccess && onSuccess();

    if (!loggedIn || !config.apiUrl) return;

    if (data?.id) {
      if (hasConfirm) {
        const ok = yield* handleActionConfirm(config);

        if (!ok) return;
      }

      yield apiClient.request({
        method: config.apiMethod,
        url: config.apiUrl,
        data: compactData(config.apiParams, { id: data?.id })
      });
    }
  } catch (error) {
    yield;
  }
}

export function* unHideItem(action) {
  const { apiClient } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const { data } = action?.payload || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'unhideItem'
  );

  try {
    onSuccess && onSuccess();

    if (!config.apiUrl) return;

    if (data?.id) {
      const ok = yield* handleActionConfirm(config);

      if (!ok) return;

      yield apiClient.request({
        method: config.apiMethod,
        url: config.apiUrl,
        params: compactData(config.apiParams, { id: data?.id })
      });
    }
  } catch (error) {
    yield;
  }
}

const sagas = [
  takeEvery('tourguide/getActions', getActions),
  takeEvery('tourguide/viewItem', viewItem),
  takeEvery('tourguide/createTour', createTour),
  takeLatest('tourguide/markAsActive', markAsActive),
  takeLatest('tourguide/hideItem', hideTour),
  takeLatest('tourguide/unHideItem', unHideItem),
  takeEvery('@PageParamAware/DONE', updateParams),
  takeEvery('tourguide/createTour/success', createTourSuccess)
];

export default sagas;
