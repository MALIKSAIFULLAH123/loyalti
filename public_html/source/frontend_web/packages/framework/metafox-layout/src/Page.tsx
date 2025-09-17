import { useGlobal, usePrevious } from '@metafox/framework';
import React from 'react';
import { MasterPageConfig, UpdatePageProps } from './types';
import { isEqual } from 'lodash';

export interface PageProps extends UpdatePageProps {
  children?: JSX.Element;
  master?: MasterPageConfig;
  loginRequired?: boolean;
}

export default function Page(props: Partial<PageProps>) {
  const prev = React.useRef<Partial<PageProps>>(props);
  const [rev, setRev] = React.useState<number>(0);
  const {
    pageName,
    pageNameAlt,
    pageNameAsGuest,
    pageParams,
    contentParams,
    master,
    loginRequired
  } = props;
  const {
    layoutBackend,
    usePreference,
    isInMasterLayout,
    useLayoutProvider,
    eventCenter,
    useLayoutPageSize,
    useLoggedIn,
    cookieBackend
  } = useGlobal();
  const isLogged = useLoggedIn();
  const masterPage = isInMasterLayout() ? master : undefined;
  const { previewDevice } = usePreference();
  const pageSize = useLayoutPageSize();
  const refreshToken = cookieBackend.get('refreshToken');

  const prevParams = usePrevious(pageParams);

  const isChangedParams = isEqual(prevParams, pageParams);

  React.useEffect(() => {
    const token = eventCenter.on('onLayoutRefresh', setRev);

    return () => eventCenter.off('onLayoutRefresh', token);
  }, [eventCenter]);

  const update = useLayoutProvider();

  prev.current = props;

  React.useEffect(() => {
    layoutBackend.change({
      pageNameAlt,
      pageName,
      pageNameAsGuest,
      pageSize,
      loginRequired,
      pageParams,
      contentParams,
      previewDevice,
      masterPage,
      isLogged,
      update,
      refreshToken
    });
  }, [
    pageName,
    isChangedParams,
    pageNameAsGuest,
    pageNameAlt,
    layoutBackend,
    pageSize,
    contentParams,
    previewDevice,
    loginRequired,
    masterPage,
    rev,
    update,
    isLogged
  ]);

  return null;
}
