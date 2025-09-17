import { APP_SERVICE_CONTEXT, Manager } from '@metafox/framework';
import { getContext } from 'redux-saga/effects';

export default function* getGlobalContext(): Generator<unknown, Manager> {
  return (yield getContext(APP_SERVICE_CONTEXT)) as Manager;
}
