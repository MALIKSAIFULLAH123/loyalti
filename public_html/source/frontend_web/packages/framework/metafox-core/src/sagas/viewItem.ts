import getGlobalContext from './getGlobalContext';
import getResourceAction from './getResourceAction';
import { put } from 'redux-saga/effects';
import { RELOAD_PAGE_META, getItem } from '@metafox/framework';

type ExtraProps = {
  refreshPageMeta?: boolean;
};

export default function* viewItem(
  appName: string,
  resourceName: string,
  id: string,
  options?: Record<string, any>,
  extra: ExtraProps = {}
) {
  const { navigate, compactUrl, location } = yield* getGlobalContext();

  const action = yield* getResourceAction(appName, resourceName, 'viewItem');
  const identity = `${appName}.entities.${resourceName}.${id}`;
  const item = yield* getItem(identity);
  const { refreshPageMeta = true } = extra || {};

  if (!action?.pageUrl && !item) return;

  const url = item?.link ?? compactUrl(action.pageUrl, { id });

  if (url === location?.pathname) {
    yield put({
      type: RELOAD_PAGE_META
    });
  } else if (refreshPageMeta) {
    // refresh seo/meta when update and view resource
    yield put({
      type: 'pageMeta/put',
      payload: { id: url, data: undefined }
    });
  }

  navigate(url, options);
}
