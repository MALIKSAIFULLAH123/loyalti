/**
 * @type: saga
 * name: story.saga.addItem
 */

import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  makeDirtyPaging,
  FormSubmitAction,
  getResourceAction,
  BasicFileItem
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { takeEvery, call, put, takeLatest, all } from 'redux-saga/effects';
import { domToPng } from 'modern-screenshot';
import {
  APP_STORY,
  HEIGHT_RATIO_SIZE,
  IMAGE_PREVIEW_BACKGROUND,
  RESOURCE_STORY
} from '../constants';
import { uploadSingleFile } from '@metafox/core/sagas/utils';

function* uploadFiles(
  apiClient: any,
  fileItems: BasicFileItem[],
  params: Record<string, any>,
  url?: string
) {
  return yield all(
    fileItems
      .filter(x => x)
      .map((item: any) => {
        if (item?.file?.temp_file) {
          return {
            temp_file: item?.file?.temp_file,
            type: item?.file?.item_type || 'photo'
          };
        }

        return uploadSingleFile(apiClient, item, params, url);
      })
  );
}

function* addPhotoStory(action: ItemLocalAction) {}

function* addTextStory(action: ItemLocalAction) {}

// return a promise that resolves with a File instance
function urltoFile({ url, filename, mimeType }: any) {
  mimeType = mimeType || (url.match(/^data:([^;]+);/) || '')[1];

  return fetch(url, { method: 'GET' })
    .then(res => {
      return res.arrayBuffer();
    })
    .then(buf => {
      return new File([buf], filename, { type: mimeType });
    });
}

function* sendItem(files, payload) {
  const { apiClient, compactUrl } = yield* getGlobalContext();

  const {
    values: data,
    action,
    method,
    form,
    pageParams,
    secondAction
  } = payload;

  const config = yield* getResourceAction(APP_STORY, RESOURCE_STORY, 'addItem');

  let tempFiles = [];

  const type = data?.type;

  try {
    const uploadFile = yield call(uploadFiles, apiClient, files, {});

    tempFiles = uploadFile.map(x => ({
      temp_file: x.temp_file,
      type: x.item_type || 'photo'
    }));

    let fileData: any = { thumb_file: tempFiles[0] };

    if (type === 'video' || type === 'photo') {
      fileData = {
        thumb_file: tempFiles[0],
        file: tempFiles[1]
      };
    }

    const response = yield apiClient.request({
      method: method || config?.apiMethod,
      url: compactUrl(action || config?.apiUrl, pageParams),
      data: { ...data, ...fileData }
    });

    yield* handleActionFeedback(response, form);

    if (['post', 'POST'].includes(method || config?.apiMethod)) {
      yield* makeDirtyPaging('story');
    }

    if (response.data?.data) {
      const { resource_name = 'user_story' } = response.data?.data;

      yield put({
        type: secondAction || `@updatedItem/${resource_name}`,
        payload: { ...data, ...response.data?.data },
        meta: {}
      });
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* submitFormAdd({
  payload,
  meta
}: FormSubmitAction & { meta: { onSuccess?: any } }) {
  if (isEmpty(payload)) return;

  const { onSuccess } = meta || {};

  const { form } = payload;

  try {
    const imageNode = document.getElementById(IMAGE_PREVIEW_BACKGROUND);

    const data = yield domToPng(imageNode, {
      scale: HEIGHT_RATIO_SIZE / imageNode?.clientHeight,
      features: {
        removeControlCharacter: false
      },
      fetch: {
        requestInit: {
          cache: 'no-cache'
        }
      }
    })
      .then(dataUrl => {
        return urltoFile({ url: dataUrl, filename: 'preview.png' }).then(
          file => {
            return file;
          }
        );
      })
      .catch(error => {
        return false;
      });

    yield sendItem(
      [
        { ...data, file: data },
        payload.values.file && { file: payload.values.file }
      ],
      payload
    );
  } catch (error) {
    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);
    typeof onSuccess === 'function' && onSuccess();
  }
}

function* updatedItem(action: ItemLocalAction) {
  const { navigate } = yield* getGlobalContext();

  yield navigate('/story');
}

const sagas = [
  takeEvery('story/addPhotoStory', addPhotoStory),
  takeEvery('story/addTextStory', addTextStory),
  takeEvery('@updatedItem/user_story', updatedItem),
  takeLatest('story/submitFormAdd', submitFormAdd)
];

export default sagas;
