/**
 * @type: saga
 * name: photo.saga.androidSupportChoosePhoto
 */

import { getGlobalContext, LocalAction } from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

export function* androidSupportChoosePhoto({
  payload,
  meta
}: LocalAction<
  { ref: React.MutableRefObject<any>; acceptTypes?: string },
  { callback?: () => void }
>) {
  const { dialogBackend } = yield* getGlobalContext();
  const { callback } = meta || {};
  const { ref, acceptTypes = 'image/*' } = payload || {};

  if (!ref?.current) return;

  const value = yield dialogBackend.present({
    component: 'photo.dialog.androidSupportChoosePhoto',
    props: {}
  });

  if (!value) return;

  if (value === 'camera') {
    ref.current.setAttribute('accept', 'image/*');
    ref.current.setAttribute('capture', 'environment');
  } else {
    ref.current.setAttribute('accept', acceptTypes);
    ref.current.removeAttribute('capture');
  }

  callback && callback();
}

const sagas = [
  takeLatest('photo/androidSupportChoosePhoto', androidSupportChoosePhoto)
];

export default sagas;
