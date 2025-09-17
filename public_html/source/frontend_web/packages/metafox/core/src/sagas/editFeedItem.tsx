/**
 * @type: saga
 * name: core.editFeedItem
 */
import {
  getGlobalContext,
  getItem,
  getResourceAction,
  handleActionConfirm,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery, put } from 'redux-saga/effects';

function* editFeedItem(action: ItemLocalAction) {
  const { identity, location } = action.payload;
  const item = yield* getItem(identity);

  const { navigate, compactUrl } = yield* getGlobalContext();

  const { embed_object } = item || {};
  const itemEmbed = yield* getItem(embed_object);
  const config = yield* getResourceAction(
    itemEmbed?.module_name,
    itemEmbed?.resource_name,
    'editFeedItem'
  );

  if (!config?.pageUrl) {
    yield put({ type: 'updateFeed', payload: { identity } });

    return false;
  }

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  const pageUrl = compactUrl(config.pageUrl, itemEmbed);

  if (location?.state?.asModal) {
    navigate(pageUrl, { replace: true });
  } else {
    navigate(pageUrl);
  }

  window.scrollTo(0, 0);
}

const sagas = [takeEvery('editFeedItem', editFeedItem)];

export default sagas;
