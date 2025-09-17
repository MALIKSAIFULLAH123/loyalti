/**
 * @type: saga
 * name: photo.formSubmit
 */
import { uploadSingleFile } from '@metafox/core/sagas/utils';
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  makeDirtyPaging
} from '@metafox/framework';
import { isEmpty } from 'lodash';
import { put, takeEvery, all } from 'redux-saga/effects';

export function* uploadMultiPhotos({
  payload: {
    values: data,
    dialog,
    action,
    method,
    form,
    dialogItem,
    pageParams,
    secondAction
  }
}: FormSubmitAction) {
  const { apiClient, dialogBackend, compactUrl } = yield* getGlobalContext();
  const { files, attachments, fileItemType, attachmentItemType, ...mainData } =
    data;
  let tempFiles = [];

  try {
    if (!isEmpty(files)) {
      tempFiles = yield all(
        files.map(item =>
          (item?.url
            ? item
            : uploadSingleFile(
                apiClient,
                item,
                {
                  item_type: fileItemType
                },
                undefined,
                (x, y) => ({
                  id: x.temp_file,
                  type: x.item_type || 'photo',
                  extra_info: y?.extra_info
                })
              ))
        )
      );
    }

    if (mainData?.add_new_album === 0) {
      delete mainData.new_album;
    }

    const response = yield apiClient.request({
      method,
      url: compactUrl(action, pageParams),
      data: { files: tempFiles, ...mainData }
    });

    if (response.data?.data) {
      const { resource_name } = response.data?.data;

      yield put({
        type: secondAction || `@updatedItem/${resource_name}`,
        payload: { ...data, ...response.data?.data },
        meta: {}
      });
    }

    yield* handleActionFeedback(response, form);

    ['post', 'POST'].includes(method) &&
      (yield* makeDirtyPaging(compactUrl(action, pageParams)));

    if (dialog) {
      if (dialogItem) {
        dialogItem.setDialogValue(response.data?.data);
      } else {
        dialogBackend.dismiss();
      }
    }
  } catch (error) {
    yield* handleActionError(error, form);
  } finally {
    form.setSubmitting(false);
  }
}

const sagas = [takeEvery('@photo/uploadMultiPhotos/submit', uploadMultiPhotos)];

export default sagas;
