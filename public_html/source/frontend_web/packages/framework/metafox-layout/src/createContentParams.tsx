/**
 * @type: service
 * name: createContentParams
 */

import { isEqual } from 'lodash';
import { useRef } from 'react';

type Obj = Record<string, any>;

export default function createContentParams(data: Obj) {
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const prev = useRef<Obj>(data);

  if (!isEqual(data, prev.current)) prev.current = data;

  return prev.current;
}
