/**
 * @type: saga
 * name: core.redirectTo
 */

import { getGlobalContext, ItemLocalAction } from '@metafox/framework';
import { compactUrl } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';

function* redirectTo(action: ItemLocalAction<{ url: string }>) {
  const {
    payload: { url, ...others }
  } = action;
  const { getPageParams, location } = yield* getGlobalContext();

  const { pathname } = location;
  const pageParams = getPageParams();

  if (!url) return;

  if (pathname === url) return;

  window.location.replace(compactUrl(url, { ...pageParams, ...others }));

  yield;
}

const sagas = [takeLatest('@redirectTo', redirectTo)];

export default sagas;
