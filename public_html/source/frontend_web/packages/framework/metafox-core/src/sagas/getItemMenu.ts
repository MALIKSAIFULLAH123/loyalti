import { select } from 'redux-saga/effects';
import { getResourceMenuSelector } from '../selectors';
import { MenuShape } from '../types';

export default function* getItemMenu(item: any, menu: string) {
  if (!item) return;

  const { resource_name } = item;
  const appName = item.module_name || resource_name;

  return (yield select(
    getResourceMenuSelector,
    appName,
    resource_name,
    menu
  )) as MenuShape;
}
