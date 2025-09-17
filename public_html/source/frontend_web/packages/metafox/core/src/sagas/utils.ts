import { BasicFileItem } from '@metafox/framework';
import { isVideoType } from '@metafox/utils';
import { get, isFunction } from 'lodash';
import { all } from 'redux-saga/effects';

export function uploadSingleFile(
  apiClient: any,
  fileItem: BasicFileItem,
  params: Record<string, any>,
  url: string = '/file',
  cb?: (x: any, y: BasicFileItem) => Record<string, any>
) {
  const formData = new FormData();
  formData.append(params.name || 'file', fileItem.file);

  Object.keys(params).forEach(name => {
    formData.append(name, params[name]);
  });

  const type = isVideoType(fileItem.file.type) ? 'video' : 'photo';

  formData.append('type', type);
  formData.append('item_type', type);
  formData.append('file_type', type);

  return apiClient
    .request({
      url,
      method: 'post',
      data: formData,
      onUploadProgress: fileItem?.onUploadProgress
    })
    .then(response => {
      const fileResult = get(response, 'data.data');

      return isFunction(cb) ? cb(fileResult, fileItem) : fileResult;
    });
}

export function* uploadFiles(
  apiClient: any,
  fileItems: BasicFileItem[],
  params: Record<string, any>,
  url?: string,
  cb?: (x: any, y: BasicFileItem) => Record<string, any>
) {
  return yield all(
    fileItems.map(item => uploadSingleFile(apiClient, item, params, url, cb))
  );
}
