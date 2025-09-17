/**
 * @type: saga
 * name: saga.resendVerify
 */
import {
  FormSubmitAction,
  ItemLocalAction,
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

export function* verifyResend({
  payload: { values, method, form, action }
}: FormSubmitAction) {
  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.request({
      method,
      url: action,
      data: values
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);
  }
}

export function* resend({
  payload: { data, module_name, resource_name, action_name }
}: ItemLocalAction<{
  resource_name: string;
  module_name: string;
  action_name: string;
  data?: Record<string, any>;
}>) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const dataSource = yield* getResourceAction(
    module_name,
    resource_name,
    action_name
  );

  if (!dataSource) return;

  try {
    const response = yield apiClient.request({
      method: dataSource.apiMethod || 'GET',
      url: compactUrl(dataSource.apiUrl, data),
      data
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const effects = [
  takeLatest('@user/verify/resend', verifyResend),
  takeLatest('user/verify/resend', resend)
];

export default effects;
