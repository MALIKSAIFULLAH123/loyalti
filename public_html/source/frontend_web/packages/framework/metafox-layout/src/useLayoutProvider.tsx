/**
 * @type: service
 * name: useLayoutProvider
 */
import * as React from 'react';
import LayoutProviderContext from './LayoutProviderContext';

export default function useLayoutProvider() {
  return React.useContext(LayoutProviderContext);
}
