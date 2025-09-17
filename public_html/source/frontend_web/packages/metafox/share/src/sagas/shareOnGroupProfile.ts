/**
 * @type: saga
 * name: shareOnGroupProfile
 */

import {
  getGlobalContext,
  getItem,
  getResourceAction,
  ItemLocalAction,
  fulfillEntity
} from '@metafox/framework';
import { isString } from 'lodash';
import { takeLatest } from 'redux-saga/effects';

const APP_GROUP = 'group';

export function* shareOnGroupProfile(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const { getPageParams } = yield* getGlobalContext();
  const pageParams: any = getPageParams();
  const item = yield* getItem(identity);

  if (!item) return;

  let embed = null;

  if (item.embed_object && isString(item.embed_object)) {
    embed = yield* getItem(item.embed_object);
  }

  const feed = item;
  const object = embed || item;
  const { jsxBackend, dialogBackend, normalization } =
    yield* getGlobalContext();
  const resourceName = object.resource_name;
  const embedView = `${resourceName}.embedItem.insideFeedItem`;

  if (!jsxBackend.get(embedView)) return;

  const dataSource = yield* getResourceAction(
    APP_GROUP,
    APP_GROUP,
    'getShareOnGroupSuggestion'
  );

  const selectedItem = yield dialogBackend.present({
    component: 'group.dialog.GroupPicker',
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

  const result = normalization.normalize(selectedItem);
  yield* fulfillEntity(result.data);
  yield dialogBackend.present({
    component: 'feed.status.statusComposerDialog',
    props: {
      pageParams,
      parentIdentity: `${APP_GROUP}.entities.${selectedItem.resource_name}.${selectedItem.id}`,
      parentType: selectedItem?.resource_name,
      data: {
        attachmentType: 'share',
        privacy: selectedItem?.privacy,
        privacy_feed: selectedItem?.privacy_feed,
        privacy_detail: selectedItem?.privacy_detail,
        parentUser,
        attachments: {
          shareItem: {
            as: 'StatusComposerAttatchedShareItem',
            type: APP_GROUP,
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
            groups: [selectedItem.id]
          }
        }
      },
      title: 'share_group',
      hidePrivacy: true
    }
  });
}

const sagas = [takeLatest('group/shareOnGroupProfile', shareOnGroupProfile)];

export default sagas;
