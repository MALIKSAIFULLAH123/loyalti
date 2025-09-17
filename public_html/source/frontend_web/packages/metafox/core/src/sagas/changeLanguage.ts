/**
 * @type: saga
 * name: core.changeLanguage
 */

import {
  getGlobalContext,
  getResourceAction,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

export function* changeLanguage({
  payload
}: ItemLocalAction<{ lang: string }>) {
  const { lang } = payload;

  const { apiClient, compactData } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    'user',
    'user',
    'updateAccountSetting'
  );

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'post',
      url: config.apiUrl,
      params: compactData(config.apiParams, { language_id: lang })
    });

    yield* handleActionFeedback(response);
    yield put({ type: 'navigate/reload' });
  } catch (error) {
    yield* handleActionError(error);
  }

  return false;
}

const sagas = [takeEvery('core/changeLanguage', changeLanguage)];

export default sagas;
