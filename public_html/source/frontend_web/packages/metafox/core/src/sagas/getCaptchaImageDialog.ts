/**
 * @type: saga
 * name: captcha_image.dialog
 */

import { LocalAction, getGlobalContext } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* showImageCaptchaDialog({
  payload
}: LocalAction<{ actionSuccess: string }>) {
  const { dialogBackend, i18n } = yield* getGlobalContext();
  const { actionSuccess, ...config } = payload;

  yield dialogBackend.present({
    component: 'captcha_image.dialog.Form',
    props: {
      title: i18n.formatMessage({ id: 'image_captcha' }),
      config
    }
  });
}

export default takeLatest(
  'captcha/image_captcha/showDialog',
  showImageCaptchaDialog
);
