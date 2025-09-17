/**
 * @type: saga
 * name: saga.userForgotPassword
 */
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { takeLatest, put } from 'redux-saga/effects';
import { getFormValues } from '@metafox/form/sagas';
import { get } from 'lodash';

export function* userForgotPassword(submitAction: FormSubmitAction) {
  const {
    payload: { method, form, action, formSchema }
  } = submitAction;
  const { apiClient, navigate } = yield* getGlobalContext();
  const values = yield* getFormValues(submitAction);

  if (!values) {
    form.setSubmitting(false);

    return;
  }

  const isCaptchaImage = formSchema?.captcha?.captcha_type === 'image_captcha';
  let errResponse;

  try {
    const response = yield apiClient.request({
      method,
      url: action,
      data: values
    });

    yield* handleActionFeedback(response);
    navigate('/login');
  } catch (error) {
    errResponse = error;

    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);

    if (isCaptchaImage) {
      const isError = get(errResponse, 'response.data.errors.captcha');

      yield put({
        type: 'captcha_image/validation/end',
        payload: isError ? errResponse : undefined
      });

      if (isError) {
        form.submitForm();
      }
    }
  }
}

export function* changePassword(submitAction: FormSubmitAction) {
  const {
    payload: { method, form, action, formSchema, secondAction },
    meta: { onSuccess }
  } = submitAction;
  const { apiClient } = yield* getGlobalContext();
  const values = yield* getFormValues(submitAction);
  const confirm = formSchema?.confirm;

  if (!values) {
    form.setSubmitting(false);

    return;
  }

  const ok = yield* handleActionConfirm({
    confirm
  });

  if (!ok) {
    form.setSubmitting(false);

    return;
  }

  try {
    const response = yield apiClient.request({
      method,
      url: action,
      data: { ...values, logout_others: ok ? 1 : 0 }
    });

    yield* handleActionFeedback(response, form);

    if (secondAction) {
      yield put({
        type: secondAction,
        payload: response?.data?.data
      });
    }

    if (onSuccess) {
      onSuccess();
    }
  } catch (error) {
    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);
  }
}

const effects = [
  takeLatest('user/forgotPassword', userForgotPassword),
  takeLatest('user/changePassword', changePassword)
];

export default effects;
