import { put } from 'redux-saga/effects';
import { deletePaginationAction } from '../actions';

export default function* deletePagination(
  identity: string,
  prefixPagingId?: string
) {
  yield put(deletePaginationAction(identity, prefixPagingId));
}
