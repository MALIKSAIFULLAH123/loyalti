/**
 * @type: saga
 * name: mailInActiveNavigate
 */
import { takeEvery } from 'redux-saga/effects';
import { getGlobalContext } from '@metafox/framework';
import { compactData } from '@metafox/utils';
import qs from 'query-string';

function* mailInActiveNavigate(action: {
  type: '@admin/mailInActiveNavigate';
  payload: {
    to: string;
    target?: string;
    apiParams?: Record<string, any>;
    apiRules?: Record<string, any>;
    values: any;
    [key: string]: any;
  };
}) {
  const { to, target, apiParams, apiRules, values, ...rest } =
    action.payload || {};
  const { navigate } = yield* getGlobalContext();

  const params = compactData(apiParams, { ...rest, ...values }, apiRules);

  const url = `${to}?${qs.stringify(params)}`;

  if (target === '_blank') {
    window.open(url);
  } else {
    navigate(url);
  }
}

const sagas = [takeEvery('@admin/mailInActiveNavigate', mailInActiveNavigate)];

export default sagas;
