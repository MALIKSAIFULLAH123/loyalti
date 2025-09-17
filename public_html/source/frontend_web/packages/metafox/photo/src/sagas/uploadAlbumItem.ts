/**
 * @type: saga
 * name: album.formSubmit
 */
import { uploadFiles } from '@metafox/core/sagas/utils';
import {
  FormSubmitAction,
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  makeDirtyPaging
} from '@metafox/framework';
import { call, put, takeEvery } from 'redux-saga/effects';

export function* uploadMultiAlbums({
  payload: {
    initialValues = {},
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
  const { items, attachments, fileItemType, attachmentItemType, ...mainData } =
    data;
  let tempFiles = [];
  try {
    // add file
    const newFiles = items.filter(photo => !photo.id);

    if (newFiles?.length) {
      tempFiles = yield call(
        uploadFiles,
        apiClient,
        newFiles,
        {
          name: 'file'
        },
        undefined,
        (x, y) => ({
          extra_info: y?.extra_info,
          id: x.temp_file,
          type: x.item_type,
          status: 'new'
        })
      );
    }

    // add file album
    const existFiles = items.filter(photo => photo.id && photo.status);

    if (existFiles?.length) {
      existFiles.forEach(item =>
        tempFiles.push({
          extra_info: item?.extra_info,
          id: item.id,
          type: item.resource_name,
          status: item?.status
        })
      );
    }

    // remove file
    const initFiles = initialValues.items;
    const currentFileIds = items.map(item => item.id);

    const payLoadRemoveFiles = initFiles
      ? initFiles.filter(file => !currentFileIds.includes(file.id))
      : [];

    if (payLoadRemoveFiles?.length) {
      payLoadRemoveFiles.forEach(item =>
        tempFiles.push({
          id: item.id,
          type: item.resource_name,
          status: 'remove'
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
      const { resource_name, id } = response.data?.data;

      yield put({
        type: secondAction || `@updatedItem/${resource_name}`,
        payload: {
          ...data,
          id
        },
        meta: {}
      });
    }

    yield* handleActionFeedback(response, form);
    yield* makeDirtyPaging(urlCompacted);

    const continueAction = response?.data?.meta?.continueAction;

    if (continueAction) {
      const { type, payload } = continueAction;
      const responseData = response.data?.data;

      yield put({
        type,
        payload: { ...payload, values: data, responseData, dialog }
      });
    }

    if (dialog) {
      if (dialogItem) {
        dialogItem.setDialogValue(response.data?.data);
      } else {
        dialogBackend.dismiss();
      }
    }
  } catch (error) {
    yield* handleActionError(error, form);

    if (error?.response?.status === 403) {
      window.location.reload();
    }
  } finally {
    form.setSubmitting(false);
  }
}

const sagas = [
  takeEvery('@album/uploadMultiAlbumItems/submit', uploadMultiAlbums)
];

export default sagas;
