/**
 * @type: saga
 * name: core.saga.handleActionFeedback
 */

import { AxiosResponse } from 'axios';
import { FormikHelpers } from 'formik';
import getGlobalContext from './getGlobalContext';
import { call, put, takeLatest } from 'redux-saga/effects';
import { isPlainObject } from 'lodash';
import moment from 'moment';

const parseArguments = (argumentList: Array<any>) => {
  if (!argumentList?.length) return {};

  const messageValues = {};

  argumentList.forEach(argument => {
    const { key, value, is_date } = argument;
    messageValues[key] = is_date ? moment(value).format('LLL') : value;
  });

  return messageValues;
};

export function* handleActionFeedback(
  response: AxiosResponse,
  form?: FormikHelpers<any>
) {
  if (!response?.data) return;

  const { message, meta, status } = response.data;

  if (message) {
    const { toastBackend } = yield* getGlobalContext();

    if (status === 'success')
      yield toastBackend.success(message, meta?.time_out_message);

    if (status === 'warning')
      yield toastBackend.warning(message, meta?.time_out_message);

    if (status === 'error')
      yield toastBackend.error(message, meta?.time_out_message);

    if (status === 'info')
      yield toastBackend.info(message, meta?.time_out_message);
  }

  if (meta?.alert && isPlainObject(meta.alert)) {
    const { dialogBackend } = yield* getGlobalContext();

    if (meta.alert?.arguments) {
      const { i18n } = yield* getGlobalContext();
      const valuesMessage = parseArguments(meta.alert?.arguments?.message);

      yield call(dialogBackend.alert, {
        ...meta.alert,
        message: i18n.formatMessage({ id: meta.alert?.message }, valuesMessage)
      });
    } else {
      yield call(dialogBackend.alert, meta.alert);
    }
  }

  if (meta?.confirm && isPlainObject(meta.confirm)) {
    const { dialogBackend } = yield* getGlobalContext();

    // TODO: what next
    yield call(dialogBackend.confirm, meta.confirm);
  }

  if (meta?.nextAction && meta.nextAction.type) {
    yield put(meta.nextAction);
  }
}

function* handleFeedback(action: { payload: AxiosResponse<any, any> }) {
  const { payload } = action;

  yield* handleActionFeedback(payload);
}

const sagas = [takeLatest('@handleActionFeedback', handleFeedback)];

export default sagas;
