import { select } from 'redux-saga/effects';
import { getLastSponsorFeed } from '@metafox/feed/selectors';

export function* getLastSponsor(pagingId: string) {
  return (yield select(getLastSponsorFeed, pagingId)) as any;
}
