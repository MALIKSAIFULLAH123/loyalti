import { select } from 'redux-saga/effects';
import { getLastPinFeed } from '@metafox/feed/selectors';

export function* getLastPin(profileId: number, pagingId: string) {
  return (yield select(getLastPinFeed, profileId, pagingId)) as any;
}
