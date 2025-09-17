/**
 * @type: service
 * name: createPageParams
 */
import { isEqualWith } from 'lodash';
import * as React from 'react';

function normalizeParams<T>(
  params: object,
  next1?: (prev: T) => T,
  next2?: (prev: T) => T,
  next3?: (prev: T) => T,
  next4?: (prev: T) => T,
  next5?: (prev: T) => T
): T {
  const prev: any = Object.assign({}, params);

  // chain this method
  if (next1) {
    Object.assign(prev, next1(prev));
  }

  // then continue.
  if (next2) {
    Object.assign(prev, next2(prev));
  } // then continue.

  if (next3) {
    Object.assign(prev, next3(prev));
  } // then continue.

  if (next4) {
    Object.assign(prev, next4(prev));
  } // then continue.

  if (next5) {
    Object.assign(prev, next5(prev));
  }

  if (next4) {
    Object.assign(prev, next4(prev));
  }

  return Object.freeze(prev);
}

export default function createPageParams<T extends object>(
  props: any,
  next1?: (prev: Partial<T>) => T,
  next2?: (prev: Partial<T>) => T,
  next3?: (prev: Partial<T>) => T,
  next4?: (prev: Partial<T>) => T,
  next5?: (prev: Partial<T>) => T
): T {
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const last = React.useRef<T>(undefined);

  const result = normalizeParams<T>(props, next1, next2, next3, next4, next5);

  const custom = (objValue, othValue) => {
    if (React.isValidElement(objValue) && React.isValidElement(othValue)) {
      return true;
    }
  };

  if (!isEqualWith(last.current, result, custom)) {
    last.current = result;
  }

  return last.current;
}
