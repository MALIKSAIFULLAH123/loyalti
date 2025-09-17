import { GlobalState } from '@metafox/framework';
import { select } from 'redux-saga/effects';

export default function* getPreference() {
  return yield select((state: GlobalState) => state.userPreference);
}
