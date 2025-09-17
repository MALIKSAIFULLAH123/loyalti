/**
 * @type: saga
 * name: share.onShareItemPress
 */
import {
  getGlobalContext,
  getItem,
  GlobalState,
  ItemLocalAction
} from '@metafox/framework';
import { select, takeEvery } from 'redux-saga/effects';

export function* openDialog(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  yield dialogBackend.present({
    component: 'share.dialog.shareItem',
    props: {}
  });
}

export function* chooseShareOption(action: ItemLocalAction) {
  const { dialogBackend } = yield* getGlobalContext();
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const shareOptions = yield select(
    (state: GlobalState) => state.share.shareOptions
  );

  if (!item) return;

  yield dialogBackend.present({
    component: 'share.dialog.ShareOptionPickerDialog',
    props: {
      identity,
      shareOptions
    }
  });
}

const sagas = [takeEvery('onShareItemPress', chooseShareOption)];

export default sagas;
