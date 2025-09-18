/**
 * @type: saga
 * name: tourguide.startTour
 */

import {
  fulfillEntity,
  getGlobalContext,
  getResourceConfig,
  handleActionError
} from '@metafox/framework';
import { put, takeLatest } from 'redux-saga/effects';
import { APP_NAME, RESOURCE_TOURGUIDE } from '../constants';
import { compactUrl } from '@metafox/utils';
import { StatusTourGuide } from '../types';

export function* startTour(action) {
  const { getPageParams, apiClient, normalization } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const { data } = action?.payload || {};

  const params: any = getPageParams();

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'viewItem'
  );

  if (!params?.pageMetaName || !config.apiUrl) return;

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
    yield put({
      type: 'tourguide/reducer/playing',
      payload: {
        tourguide_id: data?.id,
        status: StatusTourGuide.Start
      }
    });
  } catch (error) {
    yield handleActionError(error);
  }
}

const sagas = [takeLatest('tourguide/startTour', startTour)];

export default sagas;
