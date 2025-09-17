import { put } from 'redux-saga/effects';
import { putEntityAction } from '../actions';

export default function* putEntity(
  identity: string,
  data: Record<string, any>
) {
  yield put(putEntityAction(identity, data));
}
