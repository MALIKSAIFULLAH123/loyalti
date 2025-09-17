import { GlobalState } from '@metafox/framework';
import { select } from 'redux-saga/effects';
import { getSessionSelector } from '../selectors';

export default function* getSession(): Generator<
  unknown,
  GlobalState['session'],
  unknown
> {
  return (yield select(getSessionSelector)) as GlobalState['session'];
}
