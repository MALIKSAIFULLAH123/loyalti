/**
 * @type: saga
 * name: core.saga.codeGenerator
 */

import {
  getGlobalContext,
  handleActionError,
  handleActionFeedback,
  LocalAction
} from '@metafox/framework';
import { isString } from 'lodash';
import { takeEvery, call, put } from 'redux-saga/effects';

function* show() {
  const { dialogBackend } = yield* getGlobalContext();

  const result = yield call(dialogBackend.present, {
    component: 'CodeGeneratorDialog',
    props: {}
  });

  if (!result) return;

  if (isString(result)) {
    yield put({ type: result });
  } else if (isString(result?.type)) {
    yield put(result);
  }
}

function* makeCode({ payload }: LocalAction<string>) {
  const { dialogBackend } = yield* getGlobalContext();

  yield call(dialogBackend.present, {
    component: 'core.dialog.RemoteForm',
    props: {
      dataSource: {
        apiUrl: `/admincp/core/form/rad.code.make-${payload}`
      }
    }
  });
}

async function readClipboard(): Promise<string> {
  try {
    const permission = await navigator.permissions.query({
      name: 'clipboard-read'
    });

    if (permission.state === 'denied') {
      throw new Error('Not allowed to read clipboard.');
    }

    return await navigator.clipboard.readText();
  } catch (error) {
    // console.log(error.message)
  }

  return '';
}

function* addTranslation() {
  const { dialogBackend } = yield* getGlobalContext();

  let key: string = yield readClipboard();
  key = key.replace(/^\W+/m, '').replace(/\W+$/m, '');

  if (!key || !/^([a-z0-9\-_]+)::([a-z0-9\-_]+)\.([a-z0-9\-_]+)$/i.test(key)) {
    key = yield dialogBackend.prompt({
      label: 'Phrase id',
      title: 'Add New Translation',
      helperText: 'format: app::group.phrase_name'
    });
  }

  if (!key) {
    return;
  }

  yield call(dialogBackend.present, {
    component: 'core.dialog.RemoteForm',
    props: {
      dataSource: {
        apiUrl: 'admincp/core/form/localize.phrase.translate',
        apiParams: {
          key
        }
      }
    }
  });
}

function* editCurrentSeo({ payload: { url } }: LocalAction<{ url: string }>) {
  const { dialogBackend } = yield* getGlobalContext();

  yield call(dialogBackend.present, {
    component: 'core.dialog.RemoteForm',
    props: {
      dataSource: {
        apiUrl: '/admincp/seo/meta/translate',
        apiParams: {
          url
        }
      }
    }
  });
}

function* ideFix() {
  const { apiClient } = yield* getGlobalContext();

  try {
    const response = yield apiClient.get('/admincp/rad/code/make/ide-fix');

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeEvery('@core/codeGenerate/show', show),
  takeEvery('@core/codeGenerate/make', makeCode),
  takeEvery('@core/codeGenerate/addTranslation', addTranslation),
  takeEvery('@core/codeGenerate/editCurrentSeo', editCurrentSeo),
  takeEvery('@core/codeGenerate/ideFix', ideFix)
];

export default sagas;
