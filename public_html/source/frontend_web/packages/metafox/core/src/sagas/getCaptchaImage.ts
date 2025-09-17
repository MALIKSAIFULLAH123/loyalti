/**
 * @type: saga
 * name: captcha_image.token
 */

import {
  LocalAction,
  getGlobalContext,
  handleActionError
} from '@metafox/framework';
import { put, takeLatest, take } from 'redux-saga/effects';
import { FormikHelpers } from 'formik';

const Id = 'captchaImageDialog';

function* getImageCaptchaToken({
  payload
}: LocalAction<{ actionSuccess: string }>) {
  const { dialogBackend, i18n } = yield* getGlobalContext();
  const { actionSuccess, ...config } = payload;
  const values = yield dialogBackend.present({
    component: 'captcha_image.dialog.Form',
    props: {
      title: i18n.formatMessage({ id: 'image_captcha' }),
      config
    },
    // keep dialogId
    dialogId: Id
  });

  yield put({
    type: actionSuccess || 'captcha_image/token/response',
    payload: values
  });
}

function* waitingValidationToken({
  payload
}: LocalAction<{ form: FormikHelpers<any> }>) {
  const { form, forceClose } = payload;

  if (form) {
    const data = yield take('captcha_image/validation/end');
    const error = data?.payload;

    if (error) {
      yield* handleActionError(error, form);
      form.setSubmitting(false);
    } else {
      forceClose();
    }
  }
}

const sagas = [
  takeLatest('captcha/image_captcha/token', getImageCaptchaToken),
  takeLatest('@waitingCaptchaImage/start', waitingValidationToken)
];

export default sagas;
