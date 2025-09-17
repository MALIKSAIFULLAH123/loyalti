import { AppResourceAction } from '@metafox/framework/Manager';
import { select } from 'redux-saga/effects';
import { getResourceActionSelector } from '../selectors';

export default function* getResourceAction(
  appName: string,
  resource: string,
  action: string
): Generator<unknown, AppResourceAction, unknown> {
  return yield select(getResourceActionSelector, appName, resource, action);
}
