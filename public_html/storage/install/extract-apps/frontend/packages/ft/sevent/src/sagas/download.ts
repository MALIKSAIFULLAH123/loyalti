/**
 * @type: saga
 * name: sevent.saga.downloadItem
 */
import {
    getGlobalContext,
    getItem,
    handleActionError,
    ItemLocalAction,
    MFOX_API_URL
  } from '@metafox/framework';
  import { takeEvery } from 'redux-saga/effects';
  
  function download(url, token) {
    let filename = 'downloaded_file'; // Default fallback filename
    const headers = {
      Authorization: `Bearer ${token}`
    };
  
    fetch(url, { headers })
      .then((response) => {
        const contentDisposition = response.headers.get('Content-Disposition');
  
        if (contentDisposition) {
          const matches = contentDisposition.match(/filename[^;=\n]*=(['"]?)([^'";]*)\1/);
  
          if (matches && matches[2]) {
            filename = matches[2].trim(); // Ensure leading/trailing spaces are removed
            filename = filename.replace(/^_+|_+$/g, ''); // Remove leading/trailing underscores
          }
        }
  
        return response.blob();
      })
      .then((blob) => {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
      })
      .catch((error) => {
        console.error('Error downloading file:', error);
      });
  }  
  
  function* downloadItem(action: ItemLocalAction) {
    const { identity } = action.payload;
    const item = yield* getItem(identity);
  
    if (!item) return;
  
    const { compactUrl, cookieBackend } = yield* getGlobalContext();

    try {
      const url = compactUrl('/sevent/download/:id', item);
  
      const token = cookieBackend.get('token');
  
      download(`${MFOX_API_URL}${url}`, token);
    } catch (error) {
      yield* handleActionError(error);
    }
  }
  
  const sagas = [takeEvery('sevent/downloadItem', downloadItem)];
  
  export default sagas;
  