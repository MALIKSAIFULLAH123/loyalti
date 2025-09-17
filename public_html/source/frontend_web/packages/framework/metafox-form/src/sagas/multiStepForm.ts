import { put } from 'redux-saga/effects';
import { openMultiStepFormAction } from '../actions';

export default function* openMultiStepForm(data: {
  resourceName: string;
  moduleName: string;
  actionName?: string;
  identity: string;
  dialogProps?: Record<string, any>;
}) {
  yield put(openMultiStepFormAction(data));
}
