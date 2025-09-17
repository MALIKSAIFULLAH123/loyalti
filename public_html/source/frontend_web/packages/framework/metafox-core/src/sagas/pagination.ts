import { PagingMeta, PagingPayload } from '@metafox/framework';
import { put } from 'redux-saga/effects';
import { paginationAction } from '../actions';

export default function* pagination(payload: PagingPayload, meta?: PagingMeta) {
  yield put(paginationAction(payload, meta));
}
