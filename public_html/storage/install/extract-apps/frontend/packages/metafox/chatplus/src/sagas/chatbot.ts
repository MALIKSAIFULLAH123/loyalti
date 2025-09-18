/**
 * @type: saga
 * name: chatplus.saga.chatbot
 */

import {
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_CHATGPT_BOT, RESOURCE_REPORT_ITEM } from '../constants';

function* likeItem(action: ItemLocalAction) {
  const { apiClient, compactData } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const values = action?.payload || {};
  const config = yield* getResourceAction(
    APP_CHATGPT_BOT,
    RESOURCE_REPORT_ITEM,
    'likeItem'
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

function* reportItem(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();
  const { onSuccess } = action?.meta || {};
  const values = action?.payload || {};
  const dataSource = yield* getResourceAction(
    APP_CHATGPT_BOT,
    RESOURCE_REPORT_ITEM,
    'reportForm'
  );

  if (!dataSource) return;

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm',
        initialValues: values
      }
    });

    if (onSuccess) onSuccess();

    return true;
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('chatplus/chatbot/likeItem', likeItem),
  takeEvery('chatplus/chatbot/reportItem', reportItem)
];

export default sagas;
