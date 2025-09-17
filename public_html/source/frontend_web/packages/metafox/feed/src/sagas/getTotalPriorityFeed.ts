import { select } from 'redux-saga/effects';
import { getTotalPriorityFeed } from '@metafox/feed/selectors';

export function* getTotalPriority(data) {
  return (yield select(getTotalPriorityFeed, data)) as any;
}
