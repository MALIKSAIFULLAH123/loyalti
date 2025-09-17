/**
 * @type: saga
 * name: core.translateLanguage
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { franc } from 'franc';
import {
  CORE_LANGUAGE_DETECT_ACTION,
  CORE_LANGUAGE_TRANSLATE_ACTION
} from '@metafox/core/constant';
import { iso6393To1 } from 'iso-639-3';

function* translateLanguage(
  action: ItemLocalAction<
    { identity: string },
    { onSuccess?: any; onStart?: any; onFinally?: any }
  >
) {
  const { identity } = action.payload || {};
  const { onSuccess, onStart, onFinally } = action.meta || {};
  const item = yield* getItem(identity);

  if (!item) return;

  onStart && onStart();
  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'translateItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      params: compactData(config.apiParams, { id: item?.id })
    });

    const data = response.data?.data;
    onSuccess(data);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  } finally {
    onFinally && onFinally();
  }
}

function detectLanguage({
  payload,
  meta
}: ItemLocalAction<{ text: string }, { onSuccess: any }>) {
  const { text } = payload || {};
  const { onSuccess } = meta || {};
  const detect = franc(text);
  const iso = iso6393To1[detect] || 'und';
  onSuccess && onSuccess(iso);

  return false;
}

const sagas = [
  takeEvery(CORE_LANGUAGE_TRANSLATE_ACTION, translateLanguage),
  takeEvery(CORE_LANGUAGE_DETECT_ACTION, detectLanguage)
];

export default sagas;
