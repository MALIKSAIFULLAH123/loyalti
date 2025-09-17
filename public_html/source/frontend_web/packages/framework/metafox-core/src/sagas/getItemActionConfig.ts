import { AppResourceAction } from '@metafox/framework/Manager';
import { select } from 'redux-saga/effects';
import { getResourceConfigSelector } from '../selectors';

export default function* getItemActionConfig(
  item: any,
  action?: string
): Generator<unknown, AppResourceAction, unknown> {
  if (!item) return;

  const { resource_name } = item;
  const appName = item.module_name || resource_name;

  const actionConfig = yield select(
    getResourceConfigSelector,
    appName,
    resource_name,
    action
  );

  return actionConfig as any;
}
