import { FormikHelpers } from 'formik';
import { get, isArray, isEmpty, isObject, set } from 'lodash';
import moment from 'moment';
import getGlobalContext from './getGlobalContext';
import { call } from 'redux-saga/effects';

const parseArguments = (argumentList: Array<any>) => {
  if (!argumentList?.length) return {};

  const messageValues = {};

  argumentList.forEach(argument => {
    const { key, value, is_date } = argument;
    messageValues[key] = is_date ? moment(value).format('LLL') : value;
  });

  return messageValues;
};

const setObject = (errors: Record<string, string>) => {
  const result = {};

  if (!isObject(errors)) return errors;

  Object.keys(errors).forEach(name => {
    set(result, name, errors[name]);
  });

  return result;
};

export default function* handleActionError(
  error: any,
  form?: FormikHelpers<any>
) {
  if (!error) return;

  const { i18n, dispatch, dialogBackend } = yield* getGlobalContext();

  const errors = get(error, 'response.data.errors');
  const status = get(error, 'response.status');
  const code = get(error, 'code');

  if (status > 500 || code === 'ERR_NETWORK') {
    yield dialogBackend.alert({
      title: i18n.formatMessage({ id: 'oops' }),
      message: i18n.formatMessage({ id: 'gateway_timeout_message' })
    });

    return;
  }

  if (form && errors) {
    form.setErrors(setObject(errors));

    return;
  }

  const msgErrors = isObject(errors)
    ? Object.keys(errors)
        .map(err => {
          return isArray(errors[err]) ? errors[err].join(', ') : undefined;
        })
        .filter(item => item)
    : [errors];
  let message =
    msgErrors.join(' ') ||
    get(error, 'response.data.message') ||
    get(error, 'response.data.error') ||
    error.message;

  let title = 'oops';

  let argumentsError: Record<string, any> = {};

  try {
    const {
      message: messageServer,
      title: titleServer,
      action,
      arguments: argumentsServer,
      payload = {}
    } = JSON.parse(get(error, 'response.data.error'));

    if (action) {
      dispatch({ type: action, payload });

      return;
    }

    message = messageServer;
    title = titleServer;
    argumentsError = argumentsServer;
  } catch (err) {}

  if (!isEmpty(argumentsError)) {
    const valuesMessage = parseArguments(argumentsError?.message);

    yield call(dialogBackend.alert, {
      title: i18n.formatMessage({ id: title }),
      message: i18n.formatMessage({ id: message }, valuesMessage)
    });
  } else {
    yield dialogBackend.alert({
      title: i18n.formatMessage({ id: title }),
      message
    });
  }
}
