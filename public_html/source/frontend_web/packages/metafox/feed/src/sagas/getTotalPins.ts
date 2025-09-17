import { select } from 'redux-saga/effects';
import { getTotalPinsFeed } from '@metafox/feed/selectors';

export function* getTotalPins(profileId: number) {
  return (yield select(getTotalPinsFeed, profileId)) as any;
}
