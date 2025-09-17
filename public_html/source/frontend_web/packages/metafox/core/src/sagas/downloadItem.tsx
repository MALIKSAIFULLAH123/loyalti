/**
 * @type: saga
 * name: core.downloadItem
 */
import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionConfirm,
  handleActionError,
  ItemLocalAction,
  MFOX_API_URL
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function download(url, token, onSuccess) {
  let filename = '';
  const headers = {
    Authorization: `Bearer ${token}`
  };

  fetch(url, { headers })
    .then(response => {
      filename = response.headers
        .get('Content-Disposition')
        .split('filename=')[1];

      return response.blob();
    })
    .then(blob => {
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = filename.replace(/['"]+/g, '');
      link.click();

      typeof onSuccess === 'function' && onSuccess();
    })
    .catch(error => console.log(error));
}

function* downloadItem(
  action: ItemLocalAction & {
    meta: { onSuccess: () => void; onFailure: () => {} };
  }
) {
  const { compactUrl, cookieBackend, getSetting } = yield* getGlobalContext();

  const setting = getSetting('attachment');

  const { identity } = action.payload;
  const { onSuccess, onFailure } = action.meta || {};

  const item = yield* getItem(identity);

  if (!setting || !item) return;

  const config = yield* getItemActionConfig(item, 'downloadItem');

  if (!config?.apiUrl) return false;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return false;

  try {
    const url = compactUrl(config.apiUrl, item);

    const token = cookieBackend.get('token');

    yield download(`${MFOX_API_URL}${url}`, token, onSuccess);
  } catch (error) {
    typeof onFailure === 'function' && onFailure();
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('core/downloadItem', downloadItem)];

export default sagas;
