/**
 * @type: saga
 * name: updatedMarketplace
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* updatedBlog({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('marketplace', 'marketplace', id);
}

function* marketplaceActive({ payload }) {
  yield put({ type: 'marketplace/active', payload });
}

const sagas = [
  takeEvery('@updatedItem/marketplace', updatedBlog),
  takeEvery('marketplace/hover', marketplaceActive)
];

export default sagas;
