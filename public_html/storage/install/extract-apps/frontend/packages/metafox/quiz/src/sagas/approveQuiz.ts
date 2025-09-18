/**
 * @type: saga
 * name: quiz.saga.approvePendingQuiz
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* approvePendingQuiz(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { apiClient, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'approvePendingQuiz');

  if (!config.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    yield* handleActionFeedback(response);
    yield* deleteEntity(identity);

    yield* makeDirtyPaging('quiz', 'quiz?view=pending');
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('approvePendingQuiz', approvePendingQuiz)];

export default sagas;
