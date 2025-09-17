import { put } from 'redux-saga/effects';
import { makeDirtyAction } from '../actions';

export default function* makeDirtyPaging(
  domains: string,
  excludes?: string,
  updateFeed: boolean = true
) {
  yield put(makeDirtyAction(domains, excludes));

  // need to improve
  updateFeed &&
    !['feed', 'activity_post'].includes(domains) &&
    (yield put(makeDirtyAction('feed')));
}
