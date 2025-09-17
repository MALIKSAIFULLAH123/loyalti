/**
 * @type: saga
 * name: shareOnPageProfile
 */

import {
  getGlobalContext,
  getItem,
  getResourceAction,
  LocalAction
} from '@metafox/framework';
import { isString } from 'lodash';
import { takeLatest } from 'redux-saga/effects';

const APP_PAGE = 'page';

export function* shareOnPageProfile(action: LocalAction<{ identity: string }>) {
  const {
    payload: { identity }
  } = action;
  const { getPageParams } = yield* getGlobalContext();
  const item = yield* getItem(identity);
  const pageParams: any = getPageParams();

  if (!item) return;

  let embed = null;

  if (item.embed_object && isString(item.embed_object)) {
    embed = yield* getItem(item.embed_object);
  }

  const feed = item;
  const object = embed || item;

  const { jsxBackend, dialogBackend } = yield* getGlobalContext();
  const resourceName = object.resource_name;
  const embedView = `${resourceName}.embedItem.insideFeedItem`;

  if (!jsxBackend.get(embedView)) return;

  const dataSource = yield* getResourceAction(
    APP_PAGE,
    APP_PAGE,
    'getShareOnPageSuggestion'
  );

  const selectedItem = yield dialogBackend.present({
    component: 'pages.dialog.PagesPicker',
    props: {
      dataSource
    }
  });

  if (!selectedItem) return;

  const parentUser = {
    item_type: selectedItem.resource_name,
    item_id: selectedItem.id,
    name: selectedItem.title,
    id: selectedItem.id,
    resource_name: selectedItem.resource_name
  };

  yield dialogBackend.present({
    component: 'feed.status.statusComposerDialog',
    props: {
      pageParams,
      data: {
        attachmentType: 'share',
        privacy: selectedItem?.privacy,
        parentUser,
        privacy_feed: selectedItem?.privacy_feed,
        privacy_detail: selectedItem?.privacy_detail,
        attachments: {
          shareItem: {
            as: 'StatusComposerAttatchedShareItem',
            type: APP_PAGE,
            value: {
              embedView,
              feed,
              identity: object._identity,
              extra: embed
                ? {
                    context_item_type: item.resource_name,
                    context_item_id: item.id
                  }
                : undefined
            },
            pages: [selectedItem.id]
          }
        }
      },
      title: 'share_page',
      hidePrivacy: true
    }
  });
}

const sagas = [takeLatest('page/shareOnPageProfile', shareOnPageProfile)];

export default sagas;
