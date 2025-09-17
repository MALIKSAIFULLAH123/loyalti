/**
 * @type: saga
 * name: saga.coreSetting
 */
import {
  APP_BOOTSTRAP,
  APP_BOOTSTRAP_DONE,
  CACHE_SETTING_KEY,
  getGlobalContext,
  IS_ADMINCP,
  USE_BOOTSTRAP_CACHE as CACHED,
  IS_DEV,
  IS_INSTALLATION
} from '@metafox/framework';
import { ACTION_LOAD_SETTING } from '@metafox/framework/state/constants';
import { assign, get, isEmpty } from 'lodash';
import {
  all,
  put,
  take,
  takeLatest,
  race,
  call,
  fork,
  cancel,
  join
} from 'redux-saga/effects';

const settingTypes = [
  'acl',
  'actions',
  'appMenus',
  'assets',
  'forms',
  'resourceMenus',
  'settings'
];

function* fetchSetting(cachedData, type, options) {
  const revision = CACHED ? get(cachedData, `${type}._revision`, 'now') : 'now';
  const apiUrl = `core/${
    IS_ADMINCP ? 'admin' : 'web'
  }/settings/${type}/${revision}.json`;
  const { apiClient } = yield* getGlobalContext();
  const response = yield apiClient.get(apiUrl, options);
  const result = response.data.data;
  const keepCached = get(result, 'keepCached', false);
  const keepCachedAll = get(result, 'keepCachedAll', false);

  if (keepCachedAll) {
    yield put({ type: '@KEEP_CACHED_SETTING_DONE' });

    return undefined;
  }

  if (keepCached) return undefined;
  // check installation rules.

  if (
    result?.force_install &&
    result?.installation_url &&
    !IS_DEV &&
    !IS_INSTALLATION
  ) {
    window.location.href = result.installation_url;

    return undefined;
  }

  return {
    [type]: { ...result[type], _revision: result.revision }
  };
}

function* checkKeepCachedDone() {
  yield take('@KEEP_CACHED_SETTING_DONE');

  return true;
}

function* fetchAllSettings({ cachedData, options }) {
  return yield all(
    settingTypes.map(type => fetchSetting(cachedData, type, options))
  );
}

function* bootstrapSettings() {
  const { setAcl, setAssets, setSetting, localStore } =
    yield* getGlobalContext();

  try {
    let data: Record<string, any> = CACHED
      ? localStore.getJSON(CACHE_SETTING_KEY)
      : undefined;
    const controller = new AbortController();

    const settingsProcess = yield fork(fetchAllSettings, {
      cachedData: data,
      options: {
        signal: controller.signal
      }
    });
    const { end, settings = [] } = yield race({
      settings: join(settingsProcess),
      end: call(checkKeepCachedDone)
    });

    if (end) {
      controller.abort();

      yield cancel(settingsProcess);
    } else {
      const result = assign({}, ...settings.filter(x => !!x));

      if (!isEmpty(result)) {
        data = assign(data, result);
        localStore.set(CACHE_SETTING_KEY, JSON.stringify(data));
      }
    }

    // verify token me is valid or logout immediately
    yield put({ type: ACTION_LOAD_SETTING, payload: data });

    setAcl(data.acl);
    setSetting(data.settings);
    setAssets(data.assets);

    yield put({ type: '@bootstrap/settings/DONE' });
  } catch (error) {
    yield put({ type: '@bootstrap/settings/DONE', error });
  }
}

export function* bootstrap() {
  const results = yield all([
    take('@bootstrap/intl/DONE'),
    take('@bootstrap/user/DONE'),
    take('@bootstrap/theme/DONE'),
    take('@bootstrap/settings/DONE')
  ]);

  const error = results.find(action => action.error);

  yield put({
    type: APP_BOOTSTRAP_DONE,
    payload: { loaded: true, error }
  });
}

const sagas = [
  takeLatest(APP_BOOTSTRAP, bootstrap),
  takeLatest(APP_BOOTSTRAP, bootstrapSettings)
];

export default sagas;
