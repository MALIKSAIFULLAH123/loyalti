/**
 * @type: service
 * name: isInMasterPage
 */
import AsMasterPageContext from './AsMasterPageContext';
import * as React from 'react';

export default function isInMasterPage(): boolean {
  // eslint-disable-next-line react-hooks/rules-of-hooks
  return React.useContext(AsMasterPageContext);
}
