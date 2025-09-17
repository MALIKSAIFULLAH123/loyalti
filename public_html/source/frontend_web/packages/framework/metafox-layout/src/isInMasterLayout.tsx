/**
 * @type: service
 * name: isInMasterLayout
 */
import * as React from 'react';
import LayoutScopeContext from './LayoutScopeContext';

export default function isInMasterLayout() {
  // eslint-disable-next-line react-hooks/rules-of-hooks
  return React.useContext(LayoutScopeContext);
}
