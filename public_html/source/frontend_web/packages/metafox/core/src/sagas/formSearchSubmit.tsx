/**
 * @type: saga
 * name: core.formSearchSubmit
 */
import {
  FormSubmitAction,
  FORM_SEARCH_SUBMIT,
  FORM_ADMIN_SEARCH_SUBMIT,
  getGlobalContext,
  handleActionError
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { stringify } from 'query-string';
import { takeEvery } from 'redux-saga/effects';

export function* formSearchSubmitSaga({
  payload: { values: data, dialog, action, method, form, dialogItem }
}: FormSubmitAction) {
  const { navigate } = yield* getGlobalContext();

  try {
    navigate(`${action}?${stringify(data)}`);
  } catch (error) {
    yield* handleActionError(error);
  } finally {
    form.setSubmitting(false);
  }
}

export function* formAdminSearchSubmitSaga({
  payload: {
    values: data,
    dialog,
    action,
    method,
    form,
    dialogItem,
    pageParams,
    submitUrl
  }
}: FormSubmitAction) {
  const { navigate, location } = yield* getGlobalContext();
  const url = submitUrl ? compactUrl(submitUrl, pageParams) : location.pathname;

  try {
    navigate(`${url}?${stringify(data, { skipEmptyString: true })}`);
  } catch (error) {
    yield* handleActionError(error);
  } finally {
    form.setSubmitting(false);
  }
}

const sagas = [
  takeEvery(FORM_SEARCH_SUBMIT, formSearchSubmitSaga),
  takeEvery(FORM_ADMIN_SEARCH_SUBMIT, formAdminSearchSubmitSaga)
];

export default sagas;
