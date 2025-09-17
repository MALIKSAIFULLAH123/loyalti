/**
 * @type: service
 * name: useMasterPageConfig
 */
import { MasterPageConfig } from './types';
import * as React from 'react';

/**
 * keep master page state to avoid re-render
 * @param data {MasterPageConfig}
 */
export default function useMasterPageConfig(
  transformPathname: (pathname: string) => string | undefined
): MasterPageConfig {
  return React.useMemo(() => {
    return {
      transformPathname
    };
  }, [transformPathname]);
}
