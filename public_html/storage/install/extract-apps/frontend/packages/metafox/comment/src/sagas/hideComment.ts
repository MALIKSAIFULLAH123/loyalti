/**
 * @type: saga
 * name: comment.saga.hideComment
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  ItemLocalAction,
  patchEntity,
  getItemActionConfig,
  handleActionConfirm,
  handleActionFeedback,
  fulfillEntity
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { takeEvery } from 'redux-saga/effects';

function* hideGlobalComment({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_hidden } = item;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'hideGlobalItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      yield* patchEntity(identity, { is_hidden: data?.is_hidden });
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_hidden });
    yield* handleActionError(error);
  }

  return false;
}

function* unhideGlobalComment({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_hidden } = item;

  const { apiClient, compactUrl, compactData, normalization } =
    yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'unhideGlobalItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_hidden });
    yield* handleActionError(error);
  }

  return false;
}

function* hideComment({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_hidden } = item;

  const { apiClient, compactUrl, compactData } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'hideItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      yield* patchEntity(identity, { is_hidden: data?.is_hidden });
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_hidden });
    yield* handleActionError(error);
  }

  return false;
}

function* unhideComment({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { is_hidden } = item;

  const { apiClient, compactUrl, compactData, normalization } =
    yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'unhideItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'patch',
      url: compactUrl(config.apiUrl, item),
      data: compactData(config.apiParams, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);

    return true;
  } catch (error) {
    yield* patchEntity(identity, { is_hidden });
    yield* handleActionError(error);
  }

  return false;
}

function* commentPreview({ payload }: ItemLocalAction) {

  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl, normalization } =
    yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'previewComment');
 
  if (!isEmpty(item?.extra_data)) return;

  try {
    const response = yield apiClient.request({
      method: config?.apiMethod || 'GET',
      url: compactUrl(config.apiUrl, item)
    });

    if (response?.data) {
      const data = response.data?.data;
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    return true;
  } catch (error) {

    yield* handleActionError(error);
  }
  
}

const sagas = [
  takeEvery('comment/hideGlobalItem', hideGlobalComment),
  takeEvery('comment/unhideGlobalItem', unhideGlobalComment),
  takeEvery('comment/hideItem', hideComment),
  takeEvery('comment/unhideItem', unhideComment),
  takeEvery('comment/preview', commentPreview)
];

export default sagas;
