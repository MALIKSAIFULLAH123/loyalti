import { put } from 'redux-saga/effects';
import { patchEntityAction } from '../actions';

export default function* patchEntity(
  identity: string,
  data: Record<string, any>,
  deepMerge?: boolean
) {
  yield put(patchEntityAction(identity, data, deepMerge));
}
