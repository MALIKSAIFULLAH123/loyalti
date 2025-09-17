/**
 * @type: ui
 * name: layout.childPageContainer
 */
import { MainRoutes, useGlobal, useLocation } from '@metafox/framework';
import React from 'react';
import AsMasterPageContext from './AsMasterPageContext';
import ChildLayoutProvider from './ChildLayoutProvider';
import { MasterPageConfig } from './types';

type Props = {
  masterPage: MasterPageConfig;
};

export default function ChildPageContainer({ masterPage }: Props) {
  const { LayoutScopeContainer } = useGlobal();
  // can access layout provider.
  const { transformPathname } = masterPage;
  const location = useLocation();

  return (
    <LayoutScopeContainer value={false}>
      <AsMasterPageContext.Provider value={false}>
        <ChildLayoutProvider>
          <MainRoutes
            location={{
              ...location,
              masterPathname: location.pathname,
              pathname:
                transformPathname(location.pathname) || location.pathname
            }}
          />
        </ChildLayoutProvider>
      </AsMasterPageContext.Provider>
    </LayoutScopeContainer>
  );
}
