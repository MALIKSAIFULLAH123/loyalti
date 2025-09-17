/**
 * @type: saga
 * name: core.photo.quickAddToAlbum
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* quickAddToAlbum(action: ItemLocalAction) {
  const { identity, location } = action.payload;
  const item = yield* getItem(identity);

  const { navigate, compactUrl } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'editItem');

  if (!config?.pageUrl) return false;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  const pageUrl = compactUrl(config.pageUrl, item);

  if (location?.state?.asModal) {
    navigate(pageUrl, { replace: true });
  } else {
    navigate({
      pathname: pageUrl,
      search: 'add_photo=1'
    });
  }

  window.scrollTo(0, 0);
}

const sagas = [takeEvery('photo/quickAddToAlbum', quickAddToAlbum)];

export default sagas;
