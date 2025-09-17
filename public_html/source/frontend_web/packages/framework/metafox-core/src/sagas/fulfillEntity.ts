import { put } from 'redux-saga/effects';
import { fulfillEntityAction } from '../actions';

export default function* fulfillEntity(data: Record<string, any>) {
  yield put(fulfillEntityAction(data));
}
