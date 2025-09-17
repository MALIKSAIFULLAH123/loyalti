/**
 * @type: saga
 * name: core.file.upload
 */

import { getGlobalContext, handleActionError } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

type UploadAction = {
  type: string;
  payload: {
    file: File;
    params: Record<string, any>;
  };
  meta: {
    form: {
      onSuccess: (temp_file: number) => void;
      onFailure: (error: string) => void;
      onProgress?: () => void;
    };
  };
};

export function* upload(action: UploadAction) {
  const { apiClient } = yield* getGlobalContext();
  const { file, params } = action.payload;
  const { form } = action.meta;
  const formData = new FormData();
  formData.append(params.name, file);

  Object.keys(params).forEach(key => {
    formData.append(key, params[key]);
  });

  try {
    const response = yield apiClient.request({
      url: '/file',
      method: 'POST',
      data: formData
    });

    const result = response.data?.data;

    if (result.temp_file) {
      const onSuccess = form?.onSuccess;
      yield onSuccess(result.temp_file);
    }
  } catch (error) {
    const onFailure = form?.onFailure;
    yield onFailure(error as string);
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('file/UPLOAD', upload)];

export default sagas;
