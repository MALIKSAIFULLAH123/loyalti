/**
 * @type: saga
 * name: shareOnFriendProfile
 */

import {
  getGlobalContext,
  getItem,
  getResourceAction,
  ItemLocalAction
} from '@metafox/framework';
import { isString } from 'lodash';
import { takeLatest } from 'redux-saga/effects';

const APP_FRIEND = 'friend';

export function* shareOnFriendProfile(action: ItemLocalAction) {
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
  const { jsxBackend, dialogBackend } = yield* getGlobalContext();
  const resourceName = object.resource_name;
  const embedView = `${resourceName}.embedItem.insideFeedItem`;
  const dataSource = yield* getResourceAction(
    APP_FRIEND,
    APP_FRIEND,
    'shareOnFriendProfile'
  );

  if (!jsxBackend.get(embedView)) return;

  const selectedItem = yield dialogBackend.present({
    component: 'friend.dialog.FriendPicker',
    props: { ...dataSource, initialParams: dataSource?.apiParams }
  });

  if (!selectedItem) return;

  const parentUser = {
    item_type: selectedItem.resource_name,
    id: selectedItem.id,
    name: selectedItem.full_name,
    resource_name: selectedItem.resource_name
  };

  yield dialogBackend.present({
    component: 'feed.status.statusComposerDialog',
    props: {
      pageParams,
      data: {
        attachmentType: 'share',
        parentUser,
        privacy: selectedItem?.privacy,
        privacy_feed: selectedItem?.privacy_feed,
        privacy_detail: selectedItem?.privacy_detail,
        attachments: {
          shareItem: {
            as: 'StatusComposerAttatchedShareItem',
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
            type: 'friend',
            friends: [selectedItem.id]
          }
        }
      },
      title: 'share_friend',
      hidePrivacy: true
    }
  });
}

const sagas = [takeLatest('friend/shareOnFriendProfile', shareOnFriendProfile)];

export default sagas;
