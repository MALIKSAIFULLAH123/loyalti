/**
 * @type: saga
 * name: userPreference
 */
import { takeEvery } from 'redux-saga/effects';
import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';

function* userPreferenceThemeChange(action: {
  type: string;
  payload: Record<string, any>;
}) {
  const values = action.payload;
  const { onSuccess } = action?.meta || {};
  const { apiClient, compactData } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    'user',
    'user',
    'updateThemePreference'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: config.apiUrl,
      method: config?.apiMethod || 'post',
      data: compactData(config.apiParams, values)
    });

    if (onSuccess) onSuccess();

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('userPreference/themeChange', userPreferenceThemeChange)
];

export default sagas;
