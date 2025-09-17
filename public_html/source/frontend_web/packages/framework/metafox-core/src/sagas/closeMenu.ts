import { put } from 'redux-saga/effects';
import { closeMenuAction } from '../actions';

export default function* closeMenu(action: any) {
  yield put(closeMenuAction(action));
}
