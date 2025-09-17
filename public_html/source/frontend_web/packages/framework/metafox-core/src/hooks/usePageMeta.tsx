/**
 * @type: service
 * name: usePageMeta
 */

import {
  getPageMetaDataSelector,
  GlobalState,
  useLocation
} from '@metafox/framework';
import useGlobal from './useGlobal';
import { useSelector } from 'react-redux';

export default function usePageMeta() {
  const { getSetting } = useGlobal();
  const { pathname, masterPathname, search } = useLocation();
  const href = `${pathname}${search}`;
  const data = useSelector(
    (state: GlobalState) =>
      getPageMetaDataSelector(state, href) ||
      getPageMetaDataSelector(state, pathname) ||
      getPageMetaDataSelector(state, masterPathname)
  );

  const root = getSetting<{
    description: string;
    keywords: string;
    title: string;
    site_title: string;
  }>('core.general');

  return data ?? root;
}
