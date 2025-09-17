import { FormSubmitAction } from '@metafox/framework';
import { extend } from 'lodash';
import { call, put, take } from 'redux-saga/effects';

export function* getFormValues(submitAction: FormSubmitAction) {
  const {
    payload: { values: data, formSchema },
    meta
  } = submitAction;

  if (meta?.onSubmitting) {
    yield call(meta.onSubmitting);
  }

  const formValues = extend({}, data);

  if (formSchema?.captcha) {
    const action = extend(
      { type: 'captcha/token/get' },
      formSchema.captcha?.captcha_action
    );

    yield put({
      type: action?.type,
      payload: Object.assign({}, action?.payload, { formValues: data })
    });

    if (formSchema?.captcha?.captcha_type === 'image_captcha') {
      const imageCaptcha = yield take('captcha_image/token/response');

      if (!imageCaptcha?.payload) {
        return null;
      }

      // dont set formValues from payload.values, should get from dialog response(cached data)
      if (imageCaptcha?.payload?.formValues) {
        Object.assign(formValues, imageCaptcha?.payload?.formValues);
      }

      formValues.captcha = imageCaptcha?.payload?.captcha;
      formValues.image_captcha_key = imageCaptcha?.payload?.image_captcha_key;
    }

    if (formSchema?.captcha?.captcha_type === 'recaptcha_v3') {
      const result = yield take('captcha/token/response');

      formValues.captcha = result.payload;
    }
  }

  return formValues;
}

export default getFormValues;
