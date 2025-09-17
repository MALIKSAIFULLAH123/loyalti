import { select } from 'redux-saga/effects';
import { getResourceConfigSelector } from '../selectors';

export default function* getItemConfig(item: any, action?: string) {
  if (!item) return;

  const { resource_name } = item;
  const appName = item.module_name || resource_name;

  return yield select(
    getResourceConfigSelector,
    appName,
    resource_name,
    action
  );
}
