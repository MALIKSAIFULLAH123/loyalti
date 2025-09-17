import { select } from 'redux-saga/effects';
import { getItemSelector } from '../selectors';

export default function* getItem<T = any>(
  identity: string
): Generator<unknown, T, unknown> {
  return (yield select(getItemSelector, identity)) as T;
}
