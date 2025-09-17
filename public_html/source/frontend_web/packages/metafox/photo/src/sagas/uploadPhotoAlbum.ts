/**
 * @type: saga
 * name: photo_album_detail.formSubmit
 */
import { uploadFiles } from '@metafox/core/sagas/utils';
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  makeDirtyPaging
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { call, put, takeEvery } from 'redux-saga/effects';

export function* uploadMultiAlbums({
  payload: { values: data, action, method, form, pageParams }
}: FormSubmitAction) {
  const { apiClient, compactUrl } = yield* getGlobalContext();
  const { items, ...mainData } = data;
  let tempFiles = [];

  try {
    // add file
    if (isEmpty(items)) return;

    const newFiles = items.filter(photo => !photo.id);
    const uploadResults = yield call(uploadFiles, apiClient, newFiles, {
      name: 'file'
    });

    if (uploadResults?.length) {
      tempFiles = uploadResults.map(x => ({
        id: x.temp_file,
        type: x.item_type,
        status: 'new'
      }));
    }

    // add file album
    const existFiles = items.filter(photo => photo.id && photo.status);

    if (existFiles?.length) {
      existFiles.forEach(item =>
        tempFiles.push({
          id: item.id,
          type: item.resource_name,
          status: item?.status
        })
      );
    }

    const urlCompacted = compactUrl(action, pageParams);

    const response = yield apiClient.request({
      method,
      url: urlCompacted,
      data: { items: tempFiles, ...mainData }
    });

    if (response.data?.data) {
      const { id } = response.data?.data;

      if (id) {
        yield put({
          type: 'photo/photo_album/RELOAD',
          payload: {
            photo_album: id
          }
        });
      }
    }

    yield* makeDirtyPaging(urlCompacted);
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  } finally {
    form.setSubmitting(false);
  }
}

const sagas = [takeEvery('submitAlbumPhotoDetail', uploadMultiAlbums)];

export default sagas;
