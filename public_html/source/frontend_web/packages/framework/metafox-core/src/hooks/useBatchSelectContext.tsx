/**
 * @type: service
 * name: useBatchSelectContext
 */

import React from 'react';
import BatchSelectContext from '../context/BatchSelectContext';

export default function useBatchSelectContext() {
  return React.useContext(BatchSelectContext);
}
