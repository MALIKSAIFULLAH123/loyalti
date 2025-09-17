/**
 * @type: service
 * name: useIsMobile
 */
import { useGlobal } from '@metafox/framework';

const useIsMobile = (acceptTablet?: boolean) => {
  const { useLayoutPageSize } = useGlobal();
  const pageSize = useLayoutPageSize();
  const acceptSize = acceptTablet ? ['sMedium', 'small'] : ['small'];
  const isSmallSize = acceptSize.includes(pageSize);

  return isSmallSize;
};

export default useIsMobile;
