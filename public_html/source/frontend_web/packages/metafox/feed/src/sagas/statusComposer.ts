/**
 * @type: saga
 * name: feed.saga.statusComposer
 */

import { fetchDetailSaga } from '@metafox/core/sagas/fetchDetailSaga';
import { shouldHidePrivacy } from '@metafox/feed/utils';
import {
  Address,
  BasicFileItem,
  FETCH_DETAIL,
  getGlobalContext,
  getItem,
  getSession,
  handleActionError,
  handleActionFeedback,
  LinkShape,
  LocalAction,
  PAGINATION_PUSH_INDEX,
  StatusComposerState,
  getResourceAction,
  makeDirtyPaging,
  getItemsSelector
} from '@metafox/framework';
import { DEFAULT_PRIVACY } from '@metafox/share/constants';
import { getSharingItemPrivacy } from '@metafox/user/sagas';
import { isVideoType } from '@metafox/utils';
import {
  get,
  isArray,
  isEmpty,
  isMatch,
  isNil,
  pick,
  unset,
  clone,
  cloneDeep,
  set
} from 'lodash';
import {
  all,
  call,
  put,
  takeLatest,
  debounce,
  select
} from 'redux-saga/effects';
import { getTotalPriority } from './getTotalPriorityFeed';
import { APP_FEED, THROTTLE_FETCH_LINK, RESOURCE_SCHEDULE } from '../constant';
import {
  getFeedPagingIdActive,
  putPagingFeedRefresh
} from '@metafox/feed/sagas';
import collectFileItemDotFeed from '../utils/collectFileItemDotFeed';

const compareObjWithKeys = (a, b, keys) => isMatch(a, pick(b, keys));

const convertDataLink = (postConfig, link) => {
  postConfig.data.link_image = link?.image;
  postConfig.data.link_description = link?.description;
  postConfig.data.post_type = 'link';
  postConfig.data.link_title = link?.title;
  postConfig.data.link_url = link?.link;
  postConfig.data.is_preview_hidden = link?.is_preview_hidden;
};

type SubmitAction = LocalAction<
  {
    composerState: StatusComposerState;
    initValue: StatusComposerState;
    text: string;
    isEdit: boolean;
    id: string;
    parentIdentity: string;
    parentUser: Record<string, any>;
    isEditSchedule: boolean;
  },
  {
    onSuccess: (data?: any) => void;
    onFailure: (err?: any) => void;
  }
>;

export type StatusComposerPostConfig = {
  method: 'get' | 'post' | 'put' | 'patch' | 'delete';
  action: string;
  data: {
    item_type?: string;
    item_id?: string;
    user_status: string;
    post_content?: string;
    post_type: string;
    friends?: Array<number>;
    location?: Address;
    tagged_friends?: number[];
    privacy: number;
    photo_files?: Record<string, any>[];
    video_file?: Record<any, string>;
    video_url?: string;
    video_title?: string;
    video_description?: string;
    video_files?: Record<'new', number[]>;
    status_background_id?: number;
    photo_description?: string;
    parent_item_id?: string;
    post_as_parent?: boolean;
    parent_user_id?: string;
    parent_item_type?: string;
    link_image?: string;
    link_description?: string;
    link_title?: string;
    link_url?: string;
    is_preview_hidden?: number | boolean;
    poll_question?: string;
    poll_answers?: Record<string, any>[];
    poll_caption?: string;
    poll_close_time?: string;
    poll_attachments?: Record<string, any>[];
    schedule_time?: string;
  };
};

export function* onPressStatus(props) {
  const { dialogBackend } = yield* getGlobalContext();

  const privacy = yield* getSharingItemPrivacy('feed');
  const session = yield* getSession();

  const { user } = session || {};
  const { parentIdentity, parentType } = props.payload;
  const parentItem = yield* getItem(parentIdentity);
  const { privacy_detail, privacy_feed } = parentItem || {};

  const hidePrivacy = shouldHidePrivacy(parentIdentity, parentType, user);
  const privacyValue = hidePrivacy
    ? (privacy_feed || privacy_detail)?.privacy_icon
    : privacy?.value || DEFAULT_PRIVACY;

  dialogBackend.present({
    component: 'feed.status.statusComposerDialog',
    props: {
      ...props.payload,
      title: 'create_post',
      data: {
        ...props.payload.data,
        privacy: privacyValue,
        privacy_detail: privacy_feed ?? privacy_detail
      },
      hidePrivacy
    },
    dialogId: 'statusComposerDialog'
  });
}

function uploadFile(
  apiClient: any,
  fileItem: BasicFileItem
): { error?: string; uid: string; id: number } {
  const formData = new FormData();
  let params: Record<string, any> = {};

  formData.append('file', fileItem.file);
  params = {
    name: 'file',
    type: fileItem.fileItemType,
    item_type: fileItem.fileItemType,
    file_type: fileItem.fileItemType,
    file_name: fileItem.file_name,
    file_size: fileItem.file_size
  };

  // attached to file
  Object.keys(params).forEach(name => {
    formData.append(name, params[name]);
  });

  return apiClient
    .request({
      url: fileItem.upload_url,
      method: 'post',
      data: formData
    })
    .then(response => get(response, 'data.data') || get(response, 'data'))
    .catch(error => ({
      error:
        get(error, 'response.data.errors.file.0') ||
        get(error, 'response.data.error')
    }));
}

export function uploadSingleFile(
  apiClient: any,
  item: BasicFileItem,
  params: Record<string, any>,
  url: string = '/file'
) {
  if (item?.preUploadFile) {
    return {
      ...item.preUploadFile,
      text: item?.text ?? '',
      tagged_friends: item?.tagged_friends ?? []
    };
  }

  const formData = new FormData();
  formData.append(params.name, item.file);

  Object.keys(params).forEach(name => {
    formData.append(name, params[name]);
  });

  const type = isVideoType(item?.file?.type) ? 'video' : 'photo';

  if (type === 'photo' && item.base64) {
    formData.append('base64', item.base64);
  }

  formData.append('type', type);
  formData.append('item_type', type);
  formData.append('file_type', type);
  formData.append('storage_id', type);

  return apiClient
    .request({
      url,
      method: 'post',
      data: formData
    })
    .then(response => {
      const file = get(response, 'data.data');

      return {
        ...file,
        text: item?.text ?? '',
        tagged_friends: item?.tagged_friends ?? []
      };
    });
}

export function* uploadFiles(
  apiClient: any,
  items: BasicFileItem[],
  params: Record<string, any>,
  url?: string
) {
  try {
    return yield all(
      items.map(item => uploadSingleFile(apiClient, item, params, url))
    );
  } catch (err) {
    yield* handleActionError(err);
  }
}
export function* uploadAttachmentFiles(apiClient: any, items: BasicFileItem[]) {
  try {
    return yield all(items.map(item => uploadFile(apiClient, item)));
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* getLink(action: SubmitAction) {
  const {
    payload,
    meta: { onSuccess, onFailure }
  } = action;
  const { apiClient, getPageParams } = yield* getGlobalContext();
  const pageParams = getPageParams();
  const { user } = yield* getSession();

  try {
    const response = yield apiClient.request({
      method: 'post',
      url: '/link/fetch',
      data: { link: payload, owner_id: pageParams?.id || user?.id }
    });

    if (!response.status) return;

    const { data } = response.data;

    // if (image) {
    //   try {
    //     yield apiClient.request({
    //       method: 'get',
    //       url: image
    //     });
    //   } catch (err) {
    //     console.log(err);
    //     data.image = null;
    //   }
    // }

    typeof onSuccess === 'function' && onSuccess(data);
  } catch (error) {
    typeof onFailure === 'function' && onFailure(payload);
  }
}

class UploadFileError extends Error {
  response = {};

  setResponse(response: any) {
    this.response = response;
  }
}

function* postFormSubmitAttachments(payload: any) {
  const { apiClient } = yield* getGlobalContext();

  const values = cloneDeep(payload);

  const dotProps = collectFileItemDotFeed(values);

  // there are no file to upload
  if (!dotProps.length) return payload;

  const files = dotProps.map<BasicFileItem>(name => get(values, name));

  if (!files.length) return values;

  // call yield all does make failed over.
  // should improve failover
  // a file error should not stop other process
  const response = yield all(
    files.map(file =>
      call(file?.upload_url ? uploadFile : uploadSingleFile, apiClient, file, {
        name: 'file'
      })
    )
  );

  const errors = {};

  // updated collection data
  response.forEach((res, index) => {
    // just uploaded successful or null
    const path = dotProps[index];

    if (res.error) {
      set(errors, path, res.error);
    } else {
      const currentValue = get(values, path);
      set(values, path, { ...currentValue, ...res, id: res.temp_file });
    }
  });

  // error occur through uploads
  if (Object.keys(errors).length > 0) {
    const _err = new UploadFileError();
    _err.setResponse({ data: { errors } });
    throw _err;
  }

  return values;
}

export function* onSubmit(action: SubmitAction) {
  const {
    payload: {
      composerState,
      text,
      isEdit,
      isEditSchedule,
      id,
      parentIdentity,
      parentUser: parentUserProp,
      initValue
    },
    meta: { onSuccess, onFailure }
  } = action;
  const { getPageParams } = yield* getGlobalContext();
  const { profile_id } = getPageParams();

  const { apiClient, toastBackend, getSetting, compactUrl } =
    yield* getGlobalContext();
  const updateScheduleConfig = yield* getResourceAction(
    APP_FEED,
    RESOURCE_SCHEDULE,
    'updateItem'
  );
  const { user } = yield* getSession();
  const parentUser =
    (yield* getItem<{ resource_name: string; id: string }>(parentIdentity)) ||
    parentUserProp;

  const allowTagFriends = getSetting('activity.feed.enable_tag_friends');
  const friends = get(composerState, 'tags.friends.value');
  const place: Address = get(composerState, 'tags.place.value');
  const tagged_friends =
    allowTagFriends && isArray(friends) ? friends.map(x => x.id) : undefined;
  const privacy = get(composerState, 'privacy');
  const postType = get(composerState, 'post_type');
  const postAsParent = get(composerState, 'post_as_parent');
  const location = place
    ? {
        address: place.name || place.address,
        full_address: place.full_address,
        lat: place.lat,
        lng: place.lng,
        show_map: place.show_map ? 1 : 0
      }
    : undefined;

  let photos = get(composerState, 'attachments.photo.value');
  const link: LinkShape = get(composerState, 'attachments.link.value');
  const videos = get(composerState, 'attachments.video.value');
  const poll = get(composerState, 'attachments.poll.value');
  const pollAttachments = get(
    composerState,
    'attachments.poll.value.poll_attachments',
    []
  );
  const statusBackground = get(
    composerState,
    'attachments.statusBackground.value'
  );
  const initStatusBackground = get(
    initValue,
    'attachments.statusBackground.value'
  );
  const shareValue = get(composerState, 'attachments.shareItem.value');

  const advanceConfig = String(user.id) !== String(parentUser?.id) && {
    parent_item_id: parentUser?.id
  };
  const schedule_time = get(composerState, 'schedule_time.value');

  const postConfig: StatusComposerPostConfig = {
    method: isEdit
      ? 'put'
      : isEditSchedule
      ? updateScheduleConfig.apiMethod || 'put'
      : 'post',
    action: isEdit
      ? `/feed/${id}`
      : isEditSchedule
      ? compactUrl(updateScheduleConfig?.apiUrl, { id })
      : '/feed',
    data: {
      schedule_time,
      user_status: text,
      post_type: 'activity_post',
      location,
      tagged_friends,
      privacy,
      ...advanceConfig
    }
  };

  if (photos?.length) {
    photos = photos.map(photo => ({
      ...photo,
      tagged_friends: photo.tagged_friends
        ? photo.tagged_friends.map(x => pick(x, ['px', 'py', 'user_id']))
        : []
    }));
    const compareKeys = ['text', 'base64', 'tagged_friends', 'thumbnail'];
    const thumbnailKeys = ['id', 'status'];
    const payloadFiles = [];

    photos = yield call(postFormSubmitAttachments, photos);

    photos
      .filter(photo => photo.status === 'new')
      .forEach(item =>
        payloadFiles.push({
          id: item.id,
          type: item.item_type || item?.type,
          status: item.status,
          text: item.text,
          tagged_friends: item.tagged_friends,
          base64: item.base64,
          thumbnail: item?.thumbnail
            ? pick(item?.thumbnail, thumbnailKeys)
            : undefined
        })
      );

    const initPhotos = isEditSchedule
      ? []
      : initValue.attachments?.photo?.value;
    const payloadFilesEdited =
      initPhotos && initPhotos.length
        ? photos.filter(item => {
            const itemEdited = clone(
              initPhotos.find(x => item?.id && x.id === item.id)
            );

            if (!itemEdited) return false;

            if (itemEdited?.tagged_friends?.length) {
              itemEdited.tagged_friends = itemEdited.tagged_friends.map(x => ({
                user_id: x.user?.id,
                px: x.px,
                py: x.py
              }));
            }

            const isEqual = compareObjWithKeys(itemEdited, item, compareKeys);

            return !isEqual;
          })
        : [];

    payloadFilesEdited.forEach(item => {
      const updateFiels = pick(item, compareKeys);

      return payloadFiles.push({
        id: item.id,
        type: item.type || item.resource_name,
        status: item?.status || 'edit',
        ...updateFiels,
        thumbnail: item?.thumbnail?.status
          ? pick(item?.thumbnail, thumbnailKeys)
          : undefined
      });
    });

    if (isEdit) {
      const currentPhotoIds = photos.map(item => item.id);
      const payLoadRemoveFiles = initPhotos
        ? initPhotos.filter(photo => !currentPhotoIds.includes(photo.id))
        : [];

      payLoadRemoveFiles.forEach(item =>
        payloadFiles.push({
          id: item.id,
          type: item.type || item.resource_name,
          status: 'remove'
        })
      );
    }

    postConfig.data.photo_files = payloadFiles.length
      ? payloadFiles
      : undefined;
    postConfig.data.post_type = 'photo_set';
    postConfig.data.photo_description = text;
    postConfig.data.user_status = undefined;
  } else if (videos) {
    const file = yield call(
      uploadFiles,
      apiClient,
      videos,
      {
        name: 'file',
        item_type: 'v'
      },
      '/file/upload-video'
    );

    if (!file) return onFailure();

    postConfig.data.post_type = 'video';
    postConfig.data.video_description = text;
    postConfig.data.video_title = text;
    postConfig.data.video_file = {
      status: 'new',
      temp_file: file[0]
    };
  } else if ((isEdit && initStatusBackground?.id) || !isNil(statusBackground)) {
    if (isEmpty(get(composerState, 'editorStyle')))
      postConfig.data.status_background_id = isEdit ? 0 : undefined;
    else postConfig.data.status_background_id = statusBackground.id;

    if (!isNil(link)) {
      convertDataLink(postConfig, link);
    }
  } else if (poll) {
    unset(poll, 'submit');
    postConfig.data.post_type = 'poll';
    Object.keys(poll).forEach(key => (postConfig.data[key] = poll[key]));

    if (pollAttachments.length) {
      const newFiles = pollAttachments.filter(attachment => !attachment.id);
      const payloadNewFiles = yield call(
        uploadAttachmentFiles,
        apiClient,
        newFiles
      );

      if (!payloadNewFiles) return onFailure();

      const payloadAttachments = [];

      payloadNewFiles.forEach(item =>
        payloadAttachments.push({
          ...item,
          id: item.id,
          status: 'new'
        })
      );

      postConfig.data.poll_attachments = payloadAttachments.length
        ? payloadAttachments
        : undefined;
    }
  } else if (!isNil(link)) {
    convertDataLink(postConfig, link);
  } else if (shareValue && !isEdit) {
    postConfig.action = '/feed/share';

    const shareItem = yield* getItem(shareValue.identity);
    const extra = shareValue.extra;

    postConfig.data.item_type =
      shareItem.shared_item_type || shareItem.resource_name;
    postConfig.data.item_id = shareItem.shared_item_id || shareItem.id;

    if (extra?.context_item_type && extra?.context_item_id) {
      postConfig.data.context_item_type = extra?.context_item_type;
      postConfig.data.context_item_id = extra?.context_item_id;
    }

    postConfig.data.post_content = text;
    postConfig.data.post_type = get(
      composerState,
      'attachments.shareItem.type'
    );
    const parentIds = `${postConfig.data.post_type}s`;

    postConfig.data[parentIds] = get(
      composerState,
      `attachments.shareItem.${parentIds}`
    );
  }

  if (postType) {
    postConfig.data.post_type = postType;
  }

  if (postAsParent) {
    postConfig.data.post_as_parent = postAsParent;
  }

  if (isEdit) {
    // currently dont support change post type when edit
    postConfig.data.post_type = initValue?.post_type;
  }

  try {
    const response = yield apiClient.request({
      method: postConfig.method,
      url: postConfig.action,
      data: postConfig.data
    });

    const ok = 'success' === get(response, 'data.status');
    const message = get(response, 'data.message');
    const feedId = get(response, 'data.data.id');
    const feedIds = get(response, 'data.data.ids');
    const nextAction = get(response, 'data.data.nextAction');

    nextAction && (yield put(nextAction));

    yield* handleActionFeedback(response);

    if (ok && feedId === 0) {
      onSuccess();
      toastBackend.success(message);
    }

    if (!ok || (!feedId && !feedIds?.length)) {
      onSuccess();

      return;
    }

    const id = feedId || feedIds[0];

    try {
      if (schedule_time) {
        if (isEditSchedule) {
          yield* updateScheduleFeedItem(id, parentUser);
        }
      } else {
        // support for both v4, v5
        feedId && (yield* updateFeedItem(id, parentUser, !isEdit));
        feedIds &&
          (yield* feedIds.map((id: number) =>
            call(updateFeedItem, id, parentUser, !isEdit)
          ));
      }

      // eslint-disable-next-line no-empty
    } catch (e) {}

    yield* makeDirtyPaging(postConfig.data.post_type, undefined, false);

    onSuccess();

    // scroll to new post
    if (!isEdit) {
      setImmediate(() => {
        const element = document.getElementById(
          `homefeed_feed.entities.feed.${feedId}`
        );

        if (!element) return;

        const yOffset = -60;
        const yOffsetInProfile = profile_id ? -60 : 0;
        const eleTop = element.getBoundingClientRect().top;
        const heightScreen = window.innerHeight;

        if (eleTop < heightScreen) return;

        const y = eleTop + window.pageYOffset + yOffset + yOffsetInProfile;

        window.scrollTo({ top: y, behavior: 'smooth' });
      });
    }
  } catch (err) {
    onFailure();
    yield* handleActionError(err);
  }
}

export function* updateScheduleFeedItem(id: number, item: Record<string, any>) {
  const { compactUrl } = yield* getGlobalContext();
  const scheduleConfig = yield* getResourceAction(
    APP_FEED,
    RESOURCE_SCHEDULE,
    'viewItem'
  );

  if (!scheduleConfig) return;

  yield* fetchDetailSaga({
    payload: {
      apiUrl: compactUrl(scheduleConfig.apiUrl, { id }),
      apiParams: item ? { user_id: item?.id } : {}
    },
    type: FETCH_DETAIL
  });
}

function* putItemPagination(data: any[], pagingId: string, indexId: string) {
  yield put({
    type: PAGINATION_PUSH_INDEX,
    payload: {
      data,
      pagingId,
      indexId
    }
  });
}

export function* updateFeedItem(
  id: number,
  item: Record<string, any>,
  newFeed: boolean,
  exceptEntities?: string[]
) {
  const { getPageParams } = yield* getGlobalContext();
  const { profile_id } = getPageParams();

  const apiUrl = item ? `feed/${id}?user_id=${item?.id}` : `feed/${id}`;

  const pagingId = yield* getFeedPagingIdActive();

  const data = yield* fetchDetailSaga({
    payload: {
      apiUrl,
      exceptEntities
    },
    type: FETCH_DETAIL
  });

  if (!pagingId) return;

  if (newFeed) {
    const indexId = yield* getTotalPriority({
      pagingId,
      profileId: item?.id || null
    });

    yield* putPagingFeedRefresh({ excludePagingId: pagingId });

    const feeds: any = yield select(state => getItemsSelector(state, data));

    yield* feeds.map(feed => {
      // eslint-disable-next-line eqeqeq, array-callback-return
      if (profile_id && feed?.owner_id != profile_id) return;

      return call(putItemPagination, data, pagingId, indexId);
    });
  }
}

const sagas = [
  takeLatest('statusComposer/onPress/status', onPressStatus),
  debounce(THROTTLE_FETCH_LINK, 'statusComposer/getLink', getLink),
  takeLatest('statusComposer/SUBMIT', onSubmit)
];

export default sagas;
