import { put } from 'redux-saga/effects';

export default function* reloadEntity(identity: string) {
  yield put({ type: '@entity/reload', payload: { identity } });
}
