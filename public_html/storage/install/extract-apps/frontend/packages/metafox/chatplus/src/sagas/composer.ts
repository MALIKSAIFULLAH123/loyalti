/**
 * @type: saga
 * name: saga.chatplus.composer
 */

import { getGlobalContext, LocalAction } from '@metafox/framework';
import { parseFileSize, shortenFileName } from '@metafox/utils';
import { takeEvery, all } from 'redux-saga/effects';
import { ReactMode } from '../types';
import handleActionErrorChat from './handleActionErrorChat';
import { getSubscriptionItem } from './helpers';
import { uniqueId } from 'lodash';

const mappingType = {
  d: 'direct',
  p: 'private',
  c: 'public'
};

const isImage = (file: any) => {
  const fileType = file['type'];
  const validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
  const isImage = validImageTypes.includes(fileType);

  return isImage;
};

function* handleSubmit(
  action: LocalAction<
    {
      msgId?: string;
      rid: string;
      text: string;
      reactMode?: ReactMode;
    },
    {
      onFailure?: (msg: string) => void;
      onSuccess?: () => void;
    }
  >
) {
  const onSuccess = action.meta?.onSuccess;
  const onFailure = action.meta?.onFailure;

  const { msgId, rid, text, reactMode = 'no_react' } = action.payload;

  try {
    const { chatplus } = yield* getGlobalContext();

    const subscription = yield* getSubscriptionItem(rid);
    const { t, name } = subscription || {};

    const msg =
      reactMode === 'reply' && t && name
        ? `[ ](${chatplus.getConfig().chatUrl}/${
            mappingType[t]
          }/${name}?msg=${msgId})  ${text}`
        : text;

    const _id = reactMode === 'reply' ? undefined : msgId;

    if (onSuccess) yield onSuccess();

    yield chatplus.waitDdpMethod({
      name: reactMode === 'edit' ? 'updateMessage' : 'sendMessage',
      params: [{ _id, rid, msg }]
    });
  } catch (error: any) {
    if (onFailure) yield onFailure(error.message);

    yield* handleActionErrorChat(error);
  }
}

function* handleUpload(
  action: LocalAction<
    {
      files: any;
      rid: string;
      text?: string;
      reactMode?: ReactMode;
      msgId?: string;
    },
    {
      onFailure?: (msg: string) => void;
      onSuccess?: () => void;
    }
  >
) {
  const { onSuccess } = action.meta;
  const { files, rid, text, reactMode, msgId } = action.payload;

  try {
    const { chatplus, dialogBackend, i18n } = yield* getGlobalContext();

    const { MultipleImageUpload_MaxFileCount, FileUpload_MaxFileSize } =
      chatplus.getPublicSettings();

    const subscription = yield* getSubscriptionItem(rid);

    if (!subscription) return;

    const { t, name } = subscription || {};

    const imageFiles = [];
    const otherFiles = [];

    if (!files && !files.length) return;

    const customText =
      reactMode === 'reply' && t && name
        ? `[ ](${chatplus.getConfig().chatUrl}/${
            mappingType[t]
          }/${name}?msg=${msgId})  ${text}`
        : text;

    if (MultipleImageUpload_MaxFileCount < files.length) {
      yield dialogBackend.alert({
        title: 'Oops!',
        message: i18n.formatMessage(
          { id: 'could_not_select_more_than_limit_files' },
          {
            limit: MultipleImageUpload_MaxFileCount
          }
        )
      });

      return;
    }

    for (const index of Object.keys(files)) {
      const fileItemSize = files[index].size;
      const fileItemName = files[index].name;

      if (isImage(files[index])) {
        imageFiles.push(files[index]);
      } else {
        otherFiles.push(files[index]);
      }

      if (
        fileItemSize > FileUpload_MaxFileSize &&
        FileUpload_MaxFileSize !== 0
      ) {
        dialogBackend.alert({
          message: i18n.formatMessage(
            { id: 'warning_upload_limit_one_file' },
            {
              fileName: shortenFileName(fileItemName, 30),
              fileSize: parseFileSize(fileItemSize),
              maxSize: parseFileSize(FileUpload_MaxFileSize)
            }
          )
        });

        return;
      }
    }

    if (onSuccess) yield onSuccess();

    const fileList = [];

    if (imageFiles?.length) {
      fileList.push(() => chatplus.roomUpload(imageFiles, rid, customText));
    }

    if (otherFiles?.length) {
      let textMsg = !imageFiles.length ? customText : undefined;
      fileList.push(
        ...otherFiles.map((file, index) => {
          const key = uniqueId('chatplusFileUpload');

          return () => {
            if (index !== 0) textMsg = undefined;

            return chatplus.roomUpload([file], rid, textMsg, key);
          };
        })
      );
    }

    if (fileList?.length) {
      yield all(fileList.map(fn => fn && fn()));
    }
  } catch (error) {
    // if (onFailure) yield onFailure(error.message);
  }
}

const sagas = [
  takeEvery('chatplus/composer/SUBMIT', handleSubmit),
  takeEvery('chatplus/composer/upload', handleUpload)
];

export default sagas;
