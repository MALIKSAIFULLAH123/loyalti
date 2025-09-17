/**
 * @type: service
 * name: useLayoutPageSize
 */
import { useGlobal } from '@metafox/framework';
import { PageSize } from '@metafox/layout';
import React from 'react';

const useLayoutPageSize = (): PageSize => {
  const { layoutBackend, usePreference } = useGlobal();
  const { previewDevice } = usePreference();
  const [size, setSize] = React.useState<number>(window.innerWidth);
  const [pageSize, setPageSize] = React.useState<PageSize>(
    layoutBackend.getPageSizeByWidth(window.innerWidth)
  );

  React.useEffect(() => {
    const pageSize = previewDevice
      ? layoutBackend.getPageSizeByDeviceName(previewDevice)
      : layoutBackend.getPageSizeByWidth(size);
    setPageSize(pageSize);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [previewDevice, size]);

  React.useEffect(() => {
    const event = window.addEventListener('resize', () => {
      setSize(window.innerWidth);
    });

    return () => {
      window.removeEventListener('resize', event);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return pageSize;
};

export default useLayoutPageSize;
