/**
 * @type: service
 * name: usePageParams
 */
import * as React from 'react';
import PageParamsContext from './PageParamsContext';

export default function usePageParams(): Record<string, string> {
  return React.useContext(PageParamsContext);
}
