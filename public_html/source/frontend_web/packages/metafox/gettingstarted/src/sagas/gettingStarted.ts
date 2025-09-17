/**
 * @type: saga
 * name: gettingStarted.saga
 */

import {
  getGlobalContext,
  handleActionError,
  ItemLocalAction,
  getSession,
  getResourceAction,
  patchEntity
} from '@metafox/framework';
import { takeLatest, delay } from 'redux-saga/effects';
import {
  APP_GETTING_STARTED,
  RESOURCE_TODO_LIST
} from '@metafox/gettingstarted';
import { uniq } from 'lodash';

let viewedStepsBuffer = [];

export function* markAsRead(action: { type: string; payload }) {
  const { id } = action.payload;
  const { user } = yield* getSession();
  const { apiUrl, apiMethod } = yield* getResourceAction(
    APP_GETTING_STARTED,
    RESOURCE_TODO_LIST,
    'markTodoListItem'
  );

  viewedStepsBuffer.push(id);

  yield delay(1000);

  if (!id) return;

  try {
    const { apiClient } = yield* getGlobalContext();
    const uniqueSteps = uniq(viewedStepsBuffer);
    viewedStepsBuffer = [];

    yield apiClient.request({
      method: apiMethod || 'POST',
      url: apiUrl,
      data: { todo_list_ids: uniqueSteps, user_id: user.id }
    });

    for (const todoId of uniqueSteps) {
      yield* patchEntity(`getting-started.entities.todo_list.${todoId}`, {
        is_done: true
      });
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* openDialog({ payload }: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();

  try {
    yield dialogBackend.present({
      component: 'gettingStarted.dialog.start',
      props: {}
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('gettingStarted/doneStep', markAsRead),
  takeLatest('getting-started/gettingStarted', openDialog)
];

export default sagas;
