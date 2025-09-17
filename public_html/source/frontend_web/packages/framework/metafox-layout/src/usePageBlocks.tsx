/**
 * @type: service
 * name: usePageBlocks
 */
import * as React from 'react';
import BlocksContext from './BlocksContext';

export default function usePageBlocks() {
  return React.useContext(BlocksContext);
}
