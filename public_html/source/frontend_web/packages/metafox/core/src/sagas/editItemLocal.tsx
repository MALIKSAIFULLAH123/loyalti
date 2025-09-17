/**
 * @type: saga
 * name: core.editItemLocal
 */
import { ItemLocalAction, patchEntity } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

export function* editItemLocal(action: ItemLocalAction) {
  const { identity, data } = action.payload;

  if (!identity || !data) return;

  try {
    yield* patchEntity(identity, data);
  } catch (error) {}
}

const sagas = [takeEvery('editItemLocal', editItemLocal)];

export default sagas;
