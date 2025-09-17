/**
 * @type: saga
 * name: user/register
 */
import { getFormValues } from '@metafox/form/sagas';
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionError,
  handleActionFeedback
} from '@metafox/framework';
import { ACTION_LOGIN_BY_TOKEN } from '@metafox/user';
import { get } from 'lodash';
import { put, takeLatest, delay } from 'redux-saga/effects';
import { isExternalLink } from '@metafox/utils';

export function* register(submitAction: FormSubmitAction) {
  const { apiClient, navigate, getSetting, redirectTo } =
    yield* getGlobalContext();

  const {
    payload: { form, method, action, formSchema }
  } = submitAction;

  const isCaptchaImage = formSchema?.captcha?.captcha_type === 'image_captcha';
  let errResponse;

  const values = yield* getFormValues(submitAction);

  if (!values) {
    form.setSubmitting(false);

    return;
  }

  try {
    const response = yield apiClient.request({
      method,
      url: action,
      data: values,
      headers: {
        Authorization: undefined
      }
    });

    // try logged in access if token is given
    const result = get(response, 'data.data');
    const { access_token, refresh_token, expires_in, remember } = result;

    const id = get(response, 'data.data.id');
    const redirect_after_signup = getSetting('user.redirect_after_signup');
    const isVerified = get(response, 'data.data.is_verified');
    const isVerifyData = get(response, 'data.data.verification_required');
    const isVerifyEmail = get(
      response,
      'data.data.verification_required.email'
    );
    const isVerifyPhone = get(
      response,
      'data.data.verification_required.phone_number'
    );

    yield* handleActionFeedback(response);

    yield delay(5000);

    if (!isVerified) {
      if (isVerifyEmail || isVerifyPhone) {
        navigate('/verify-account', { state: { data: isVerifyData } });

        return;
      }
    }

    if (access_token) {
      yield put({
        type: ACTION_LOGIN_BY_TOKEN,
        payload: {
          token: access_token,
          refreshToken: refresh_token,
          expiresIn: expires_in,
          remember,
          urlForceRedirect: redirect_after_signup
        }
      });

      return;
    }

    if (id) {
      if (isExternalLink(redirect_after_signup)) {
        redirectTo(redirect_after_signup);

        return;
      }

      navigate(redirect_after_signup || '/login', {
        replace: true
      });
    }
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

const effects = [takeLatest('user/register', register)];

export default effects;
