/**
 * @type: saga
 * name: blog.saga.updatedItem
 */

import { LocalAction, viewItem } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updatedItem({ payload: { id } }: LocalAction<{ id: string }>) {
  yield* viewItem('blog', 'blog', id);
}

const sagas = [takeEvery('@updatedItem/blog', updatedItem)];

export default sagas;
