/**
 * @type: saga
 * name: saga.multiStepForm
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* openMultiStepForm(
  action: ItemLocalAction & {
    payload: {
      identity: string;
      resourceName: string;
      moduleName: string;
      actionName?: string;
      dialogProps: Record<string, any>;
    };
  }
) {
  try {
    const {
      identity,
      resourceName,
      moduleName,
      actionName,
      dialogProps = {}
    } = action.payload;

    const item = yield* getItem(identity);

    const { dialogBackend } = yield* getGlobalContext();
    const dataSource = yield* getItemActionConfig(
      { resource_name: resourceName, module_name: moduleName },
      actionName
    );

    if (!dataSource) return;

    yield dialogBackend.present({
      component: 'core.dialog.MultiStepForm',
      props: {
        ...dialogProps,
        dataSource,
        pageParams: { id: item.id }
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* nextMultiStepForm(
  action: ItemLocalAction<{
    values: Record<string, any>;
    processChildId: string;
    previousProcessChildId: string;
    formName: string;
    responseData: Record<string, any>;
  }>
) {
  try {
    const {
      values,
      processChildId,
      previousProcessChildId,
      formName,
      responseData
    } = action.payload;

    // save pre form data to redux.
    yield put({
      type: 'formValues/multiForm/nextStep',
      payload: {
        formName,
        processChildId: previousProcessChildId,
        data: values
      }
    });

    // save new form schema to redux.
    yield put({
      type: 'formSchemas/multiForm/nextStep',
      payload: {
        formName,
        processChildId,
        data: {
          formSchema: { ...responseData, formName: processChildId },
          previousProcessChildId
        }
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* submitMultiForm(
  action: ItemLocalAction<{ resource_name: string; dialog?: boolean }>
) {
  const { dialogBackend } = yield* getGlobalContext();
  const { type, payload } = action || {};
  const { resource_name, dialog } = payload;

  if (dialog && dialogBackend) {
    dialogBackend.dismiss();
  }

  yield put({
    type: `${type}/${resource_name}`
  });
}

const sagas = [
  takeEvery('multiStepForm/init', openMultiStepForm),
  takeEvery('multiStepForm/next', nextMultiStepForm),
  takeEvery('multiStepForm/done', submitMultiForm)
];

export default sagas;
