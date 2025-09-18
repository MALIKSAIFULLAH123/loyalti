/**
 * @type: saga
 * name: saga.group.membershipQuestion
 */

import {
  deleteEntity,
  FORM_SUBMIT,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_REFRESH,
  patchEntity
} from '@metafox/framework';
import { compactData, compactUrl } from '@metafox/utils';
import qs from 'querystring';
import { put, takeEvery } from 'redux-saga/effects';
import { APP_GROUP } from '../constant';
import { TypeQuestion } from '../types';

export function* updateMembershipQuestionForm(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const { apiUrl } = yield* getItemActionConfig(
    { resource_name: 'group_question', module_name: 'group' },
    'formUpdate'
  );

  yield dialogBackend.present({
    component: 'group.manager.dialog.membershipQuestion',
    props: {
      dataSource: {
        apiUrl: compactUrl(apiUrl, { id: item.id })
      }
    }
  });
}

function* reloadGroupMembershipQuestion(action: ItemLocalAction) {
  const {
    payload: { group_id }
  } = action;
  const { apiUrl, apiParams = 'group_id=:id' } = yield* getItemActionConfig(
    { resource_name: 'group_question', module_name: 'group' },
    'viewAll'
  );

  const pagingId = `${apiUrl}?${qs.stringify(
    compactData(apiParams, { id: group_id })
  )}`;

  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl,
      apiParams,
      pagingId
    }
  });
}

export function* addMembershipQuestionForm(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  if (!item) return;

  const { apiUrl, apiParams } = yield* getItemActionConfig(
    { resource_name: 'group_question', module_name: 'group' },
    'formStore'
  );

  yield dialogBackend.present({
    component: 'group.manager.dialog.membershipQuestion',
    props: {
      dataSource: {
        apiUrl,
        apiParams: compactData(apiParams, { id: item.id })
      }
    }
  });
}

function* addGroupMembershipQuestion(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const { payload } = action;

  const { question, type_id, options, group_id } = payload.values;

  const newValue = {
    group_id,
    question,
    type_id,
    options
  };

  yield put({
    type: FORM_SUBMIT,
    payload: { ...payload, values: newValue },
    meta: {}
  });
}

function* updateGroupMembershipQuestion(
  action: ItemLocalAction & { payload: Record<string, any> } & {
    meta: { onSuccess: () => void; onFailure: () => {} };
  }
) {
  const { payload } = action;

  try {
    const { question, type_id, options, group_id } = payload.values;

    const newValue = {
      group_id,
      question,
      type_id,
      options: type_id !== TypeQuestion.FreeAnswer ? options : undefined
    };

    yield put({
      type: FORM_SUBMIT,
      payload: { ...payload, values: newValue },
      meta: {}
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* removeGroupMembershipQuestion(
  action: ItemLocalAction & {
    payload: Record<string, any>;
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity }
  } = action;

  const { apiClient, i18n } = yield* getGlobalContext();
  const { apiUrl } = yield* getItemActionConfig(
    { resource_name: 'group_question', module_name: 'group' },
    'deleteItem'
  );

  const ok = yield* handleActionConfirm({
    confirm: {
      message: i18n.formatMessage({
        id: 'are_you_sure_you_want_to_delete_this_question'
      })
    },
    apiUrl: ''
  });

  if (!ok) return;

  try {
    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: 'delete',
      url: compactUrl(apiUrl, { id })
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* showMembershipDialogPreview(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  if (!item?.has_membership_question) return;

  yield dialogBackend.present({
    component: 'group.dialog.previewQuestionDialog',
    props: {
      id: item.id,
      ...action.payload
    }
  });
}

export function* updateMembershipQuestionSuccess(
  action: ItemLocalAction & { payload: { id: string } }
) {
  const {
    payload: { id }
  } = action;

  const identity = `group.entities.group.${id}`;

  try {
    const item = yield* getItem(identity);

    if (!item) return;

    yield* patchEntity(identity, action.payload);
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* updateMembershipQuestionConfirmation(
  action: ItemLocalAction & {
    payload: { identity: string; is_answer_membership_question: boolean };
  } & { meta: { onSuccess: () => void; onFailure: () => {} } }
) {
  const {
    payload: { identity, is_answer_membership_question },
    meta: { onSuccess, onFailure }
  } = action;
  const { apiClient, normalization } = yield* getGlobalContext();

  try {
    const dataSource = yield* getItemActionConfig(
      { module_name: APP_GROUP, resource_name: APP_GROUP },
      'updateAnswerMembershipQuestion'
    );

    const { apiUrl, apiMethod, apiParams } = dataSource;

    const id = identity.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id,
        is_answer_membership_question: +is_answer_membership_question
      })
    });

    const data = response?.data?.data;
    const result = normalization.normalize(data);
    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);
    typeof onSuccess === 'function' && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
    typeof onFailure === 'function' && onFailure();
  }
}

const sagas = [
  takeEvery('@group/addNewMembershipQuestion', addGroupMembershipQuestion),
  takeEvery('group/addNewMembershipQuestionForm', addMembershipQuestionForm),
  takeEvery('group/removeMembershipQuestion', removeGroupMembershipQuestion),
  takeEvery('@group/updateMembershipQuestion', updateGroupMembershipQuestion),
  takeEvery('updateMembershipQuestion', updateMembershipQuestionForm),
  takeEvery('group/showMembershipDialog', showMembershipDialogPreview),
  takeEvery('@updatedItem/group_question', reloadGroupMembershipQuestion),
  takeEvery(
    'updateMembershipQuestion/SUCCESS',
    updateMembershipQuestionSuccess
  ),
  takeEvery(
    'group/updateMembershipQuestionConfirmation',
    updateMembershipQuestionConfirmation
  )
];

export default sagas;
