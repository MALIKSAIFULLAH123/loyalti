/**
 * @type: service
 * name: compactData
 */

import { isString, get } from 'lodash';
import qs from 'query-string';
import whenParamRules from './whenParamRules';

type Params = Record<string, any>;

function ensureObject(input: string | Params): Params {
  return isString(input) ? qs.parse(input) : { ...input } ?? {};
}

export default function compactData(
  obj1: Params | string | undefined,
  obj2: Params | string | undefined,
  rules?: Record<string, any>,
  strict?: boolean
): Record<string, any> {
  const result = {};
  const x = ensureObject(obj1);
  const y = ensureObject(obj2);

  if (!strict) {
    // always accept page.
    if (!x.q) {
      x.q = ':q';
    }

    if (!x.page) {
      x.page = ':page';
    }

    if (!x.limit) {
      x.limit = ':limit';
    }
  }

  for (const key in x) {
    let value = x[key];

    if (isString(value) && /^:[\w\.]+$/.test(value)) {
      value = get(y, value.substring(1));
    }

    if (value !== undefined) result[key] = value;
  }

  return whenParamRules(result, rules);
}
