/**
 * @type: saga
 * name: tourguide.newStep
 */

import {
  fulfillEntity,
  getGlobalContext,
  getResourceConfig,
  GridRowAction,
  handleActionError
} from '@metafox/framework';
import { takeLatest, all } from 'redux-saga/effects';
import { APP_NAME, RESOURCE_TOURGUIDE, TOURGUIDE_NEW_STEP } from '../constants';
import { compactUrl } from '@metafox/utils';
import { drawElementSelected } from '../utils';
import { cloneDeep } from 'lodash';

function* newStepFromAdmin({ payload, meta: { apiRef } }: GridRowAction) {
  try {
    const { cookieBackend } = yield* getGlobalContext();

    if (!apiRef.current) return;

    const url = apiRef.current.config?.extraData?.tour_guide_page_url;
    const tour_guide_id = apiRef.current.config?.extraData?.tour_guide_id;

    if (tour_guide_id) {
      cookieBackend.set(TOURGUIDE_NEW_STEP, JSON.stringify({ tour_guide_id }));
    }

    window.open(url);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* newStepLocalStore(action) {
  const { apiClient, normalization } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const { data } = action?.payload || {};

  const config = yield* getResourceConfig(
    APP_NAME,
    RESOURCE_TOURGUIDE,
    'viewItem'
  );

  if (!config?.apiUrl) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, { ...data })
    });

    const result = response.data?.data;

    if (result) {
      if (result?.steps?.length) {
        yield all(
          result?.steps.map(item => drawElementSelected(item?.element))
        );
      }

      const _data = cloneDeep(result);
      const _normalization = normalization.normalize(_data);
      yield* fulfillEntity(_normalization.data);
    }

    onSuccess && onSuccess(result);
  } catch (error) {
    yield;
  }
}

const sagas = [
  takeLatest('tourguide/admin/newStep', newStepFromAdmin),
  takeLatest('tourguide/newStepLocalStore', newStepLocalStore)
];

export default sagas;
