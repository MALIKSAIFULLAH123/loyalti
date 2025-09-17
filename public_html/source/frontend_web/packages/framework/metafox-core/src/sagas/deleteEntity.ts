import { put } from 'redux-saga/effects';
import { deleteEntityAction } from '../actions';

export default function* deleteEntity(identity: string) {
  yield put(deleteEntityAction(identity));
}
