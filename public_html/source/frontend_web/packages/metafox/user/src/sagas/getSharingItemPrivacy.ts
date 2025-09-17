import { select } from 'redux-saga/effects';
import {
  getAllPrivacySelector,
  getPrivacyPrivacySelector
} from '../selectors/getPrivacyPrivacy';

export function* getSharingItemPrivacy<T = any>(
  module_id: string
): Generator<unknown, T, unknown> {
  return (yield select(getPrivacyPrivacySelector, module_id)) as T;
}

export function* getAllSharingItemPrivacy<T = any>(): Generator<
  unknown,
  T,
  unknown
> {
  return (yield select(getAllPrivacySelector)) as T;
}
