import { select } from 'redux-saga/effects';
import { getAppMenuSelector } from '../selectors';
import { MenuShape } from '../types';

export default function* getAppMenu(appName: string, menuName: string) {
  return (yield select(getAppMenuSelector, appName, menuName)) as MenuShape;
}
