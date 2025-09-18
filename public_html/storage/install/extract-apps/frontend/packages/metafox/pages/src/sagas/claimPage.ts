/**
 * @type: saga
 * name: saga.pages.claimPage
 */
import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  getItemActionConfig,
  handleActionFeedback,
  handleActionError,
  fulfillEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_PAGE, RESOURCE_CLAIM } from '../constant';
import { compactData } from '@metafox/utils';

export function* claimPage(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  const { id } = item;

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_CLAIM, module_name: APP_PAGE },
    'addItem'
  );

  dialogBackend.present({
    component: 'core.dialog.RemoteForm',
    props: {
      dataSource: {
        apiUrl: compactUrl(config.apiUrl, { id })
      },
      maxWidth: 'xs'
    }
  });
}

export function* editItem(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);

  const { id } = item;

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_CLAIM, module_name: APP_PAGE },
    'editItem'
  );

  dialogBackend.present({
    component: 'core.dialog.RemoteForm',
    props: {
      dataSource: {
        apiUrl: compactUrl(config.apiUrl, { id })
      },
      maxWidth: 'xs'
    }
  });
}

export function* resubmit(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl, normalization } = yield* getGlobalContext();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_CLAIM, module_name: APP_PAGE },
    'resubmit'
  );

  if (!item) return;

  try {
    const { apiMethod, apiUrl } = config;

    const response = yield apiClient.request({
      method: apiMethod || 'post',
      url: compactUrl(apiUrl, { id: item.id })
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* cancelRequest(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;

  const item = yield* getItem(identity);
  const { apiClient, compactUrl, getPageParams, normalization } =
    yield* getGlobalContext();
  const pageParams = getPageParams();
  const config = yield* getItemActionConfig(
    { resource_name: RESOURCE_CLAIM, module_name: APP_PAGE },
    'cancelRequest'
  );

  if (!item) return;

  try {
    const { apiMethod, apiUrl } = config;

    const response = yield apiClient.request({
      method: apiMethod || 'post',
      url: compactUrl(apiUrl, { id: item.id }),
      params: compactData(config.apiParams, pageParams)
    });

    const data = response.data?.data;

    if (data) {
      const result = normalization.normalize(data);
      yield* fulfillEntity(result.data);
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('page_claim/addItem', claimPage),
  takeEvery('page_claim/editItem', editItem),
  takeEvery('page_claim/resubmit', resubmit),
  takeEvery('page_claim/cancelRequest', cancelRequest)
];

export default sagas;
