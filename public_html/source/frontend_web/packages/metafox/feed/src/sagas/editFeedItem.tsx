/**
 * @type: saga
 * name: core.updateFeedItem
 */
import { StatusComposerDialogProps } from '@metafox/feed/dialogs/StatusComposer/Base';
import { shouldHidePrivacy } from '@metafox/feed/utils';
import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getSession,
  ItemLocalAction
} from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { alpha } from '@mui/material';
import { takeEvery } from 'redux-saga/effects';

const embedObjectToAttachment = ({
  embed_object,
  status_background_id,
  post_type,
  feed
}: Record<string, any>) => {
  const embedState = Object.assign({}, embed_object);

  const { resource_name, module_name, id } = embedState;
  let attachments: any = {};

  if (post_type === 'share') {
    const embedView = `${resource_name}.embedItem.insideFeedItem`;

    return {
      attachmentType: 'share',
      attachments: {
        shareItem: {
          as: 'StatusComposerAttatchedShareItem',
          value: {
            embedView,
            feed,
            identity: `${module_name}.entities.${resource_name}.${id}`,
            extra: embedState
              ? {
                  context_item_type: feed.resource_name,
                  context_item_id: feed.id
                }
              : undefined
          }
        }
      }
    };
  }

  switch (resource_name) {
    case 'link':
      attachments = {
        link: {
          as: 'StatusComposerControlPreviewLink',
          value: embedState
        }
      };
      break;
    case 'photo':
      attachments = {
        photo: {
          as: 'StatusComposerControlAttachedPhotos',
          value: [embedState]
        }
      };
      break;
    case 'photo_set':
      attachments = {
        photo: {
          as: 'StatusComposerControlAttachedPhotos',
          value: embedState.photos
        }
      };
      break;
  }

  if (status_background_id) {
    attachments = {
      link: resource_name === 'link' ? attachments.link : undefined,
      statusBackground: {
        value: {
          id: status_background_id
        }
      }
    };

    return { attachments, attachmentType: 'backgroundStatus' };
  }

  return { attachments, attachmentType: resource_name };
};

function* deleteFeed(action: ItemLocalAction & { payload: { id: string } }) {
  const { id } = action.payload;

  yield* deleteEntity(`feed.entities.feed.${id}`);
}

function* updateFeed(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { owner, parent_user } = item || {};
  const parentIdentity = owner || parent_user;
  const parentUser = yield* getItem(parentIdentity);
  const { user } = yield* getSession();

  const { dialogBackend, apiClient, compactUrl } = yield* getGlobalContext();

  const hidePrivacy = shouldHidePrivacy(
    parentUser?._identity,
    parentUser?.module_name,
    user
  );
  const disabledPrivacy = !item?.extra?.can_change_privacy_from_feed;

  try {
    const config = yield* getItemActionConfig(item, 'editItem');

    if (!config.apiUrl) return;

    const rs = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item)
    });

    if (!rs?.data?.data?.item) return;

    const {
      status_background_id,
      privacy,
      tagged_friends,
      location,
      link,
      embed_object,
      privacy_detail
    } = rs.data.data.item;

    const { post_type, extra } = rs.data.data;

    let tags = {};
    const { attachments, attachmentType } = embedObjectToAttachment({
      embed_object,
      status_background_id,
      post_type,
      feed: rs.data.data.item
    });

    let { status_text } = rs.data.data.item;

    if (tagged_friends) {
      tags = {
        ...tags,
        friends: {
          as: 'StatusComposerControlTaggedFriends',
          priority: 1,
          value: tagged_friends
        }
      };
    }

    if (location) {
      tags = {
        ...tags,
        place: {
          value: location,
          as: 'StatusComposerControlTaggedPlace',
          priority: 3
        }
      };
    }

    if (link && !status_text) {
      status_text = link;
    }

    const status_background = yield* getItem(item?.status_background);

    const { image: bgImage, text_color } = status_background || {};

    const data = status_background_id
      ? {
          className: 'withBackgroundStatus',
          textAlignment: 'center',
          attachmentType: 'backgroundStatus',
          editorStyle: {
            fontSize: '28px',
            color: text_color || 'white',
            fontWeight: 'bold',
            textAlign: 'center',
            backgroundSize: 'cover',
            backgroundImage: `url("${getImageSrc(bgImage, '1024')}")`,
            minHeight: 371,
            marginTop: '16px',
            marginBottom: '16px',
            ...(text_color
              ? {
                  '& .editor-placeholder': {
                    color: alpha(text_color, 0.7)
                  }
                }
              : {})
          }
        }
      : {};

    // TODO: need to update data props in future
    yield dialogBackend.present<void, StatusComposerDialogProps>({
      component: 'feed.status.statusComposerDialog',
      props: {
        data: {
          ...data,
          privacy,
          tags,
          attachments,
          attachmentType,
          post_type,
          privacy_detail,
          extra
        },
        editor: {
          status_text,
          status_background_id
        },
        title: 'edit_post',
        isEdit: true,
        id: item.id,
        parentIdentity,
        parentType: parentUser?.resource_name,
        hidePrivacy,
        disabledPrivacy
      }
    });
  } catch (err) {
    // console.log(err);
  }
}

const sagas = [
  takeEvery('updateFeed', updateFeed),
  takeEvery('feed/delete', deleteFeed)
];

export default sagas;
