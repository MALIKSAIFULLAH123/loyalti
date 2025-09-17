import { select } from 'redux-saga/effects';
import { getResourceConfigSelector } from '../selectors';

export default function* getResourceConfig(
  appName: string,
  resource: string,
  action?: string
) {
  return yield select(getResourceConfigSelector, appName, resource, action);
}
