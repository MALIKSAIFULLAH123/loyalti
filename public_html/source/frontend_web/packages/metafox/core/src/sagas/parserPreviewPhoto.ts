/**
 * @type: saga
 * name: core.fetchPreviewPhoto
 */
import {
  BasicFileItem,
  FETCH_PREVIEW_PHOTO,
  getGlobalContext,
  LocalAction
} from '@metafox/framework';
import { get, isArray } from 'lodash';
import { takeEvery, call } from 'redux-saga/effects';
import { isVideoType } from '@metafox/utils';

/**
 * Execute upload get error result or id of uploaded file
 * - result.error: describe error message
 * - result.id: identity of uploaded item in database
 * - other from response.data
 *
 * @param {ApiClient} apiClient - axios instance
 * @param {BasicFileItem} fileItem -
 * @returns { error?:string, id?: number}
 *
 */

function* uploadSingleFile(
  item: BasicFileItem,
  url: string = '/file',
  callback,
  onUploadProgress,
  abortController
) {
  const { apiClient } = yield* getGlobalContext();
  const formData = new FormData();
  let params: Record<string, any> = {};
  const file = item?.file;

  const type = isVideoType(item.file.type) ? 'video' : 'photo';

  params = {
    file: item.file,
    type: item.file_type || type,
    item_type: item.item_type || item.itemType || type,
    file_type: item.file_type || type,
    file_name: item.file_name || file.name,
    file_size: item.file_size,
    base64: item.base64,
    thumbnail_sizes: item?.thumbnail_sizes,
    storage_id: item?.storage_id ?? type ?? null
  };

  // attached to file
  Object.keys(params).forEach(name => {
    if (!params[name]) return;

    if (isArray(params[name])) {
      const arr = params[name];

      for (let i = 0; i < arr.length; i++) {
        formData.append(`${name}[]`, arr[i]);
      }
    } else {
      formData.append(name, params[name]);
    }
  });
  const response = yield apiClient.request({
    url,
    method: 'post',
    data: formData,
    onUploadProgress,
    signal: abortController?.signal
  });
  const result = get(response, 'data.data');

  if (result) {
    callback && callback({ preUploadFile: result, source: result?.url });

    return result;
  }
}

export function* parserPhoto({
  payload,
  meta
}: LocalAction<
  {
    item: BasicFileItem;
  },
  {
    onUploadProgress?: () => void;
    onParseFile?: () => void;
    onError?: (err: any) => void;
    abortController?: any;
  }
>) {
  const { item } = payload;
  const { onUploadProgress, onParseFile, onError, abortController } = meta;
  const { handleActionError } = yield* getGlobalContext();

  try {
    yield call(
      uploadSingleFile,
      item,
      undefined,
      onParseFile,
      onUploadProgress,
      abortController
    );
  } catch (error) {
    if (error?.code === 'ERR_CANCELED') {
      return;
    }

    handleActionError(error);
    onError && onError(error);
  }
}

const sagas = takeEvery(FETCH_PREVIEW_PHOTO, parserPhoto);

export default sagas;
