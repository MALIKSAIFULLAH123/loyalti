/**
 * @type: saga
 * name: core.getStatics
 */
import {
  getGlobalContext,
  getResourceAction,
  getSession,
  GET_STATICS,
  patchEntity
} from '@metafox/framework';
import { compactData, compactUrl } from '@metafox/utils';
import { takeEvery } from 'redux-saga/effects';

function* getStatics(action: {
  type: string;
  payload: any;
  meta: { onSuccessStatics: () => void };
}) {
  const { apiClient } = yield* getGlobalContext();
  const { user } = yield* getSession();

  const { pageParams } = action.payload;
  const { onSuccessStatics } = action.meta;
  const { appName, resourceName } = pageParams;
  const configStas = yield* getResourceAction(
    appName,
    resourceName,
    'viewStats'
  );

  const configUserStas = yield* getResourceAction(
    appName,
    resourceName,
    'viewUserStats'
  );

  try {
    const stasResponse = yield apiClient.request({
      method: configStas.apiMethod,
      url: compactUrl(configStas.apiUrl, pageParams),
      params: compactData(configStas.apiParams, pageParams)
    });

    const userResponse = yield apiClient.request({
      method: configUserStas.apiMethod,
      url: compactUrl(configUserStas.apiUrl, {
        ...pageParams,
        user_id: user.id
      }),
      params: compactData(configUserStas.apiParams, pageParams)
    });

    yield* patchEntity(pageParams.identity, stasResponse.data.data);
    yield* patchEntity(`user.entities.user.${user.id}`, userResponse.data.data);
  } catch (error) {
  } finally {
    onSuccessStatics();
  }
}

const sagas = [takeEvery(GET_STATICS, getStatics)];

export default sagas;
