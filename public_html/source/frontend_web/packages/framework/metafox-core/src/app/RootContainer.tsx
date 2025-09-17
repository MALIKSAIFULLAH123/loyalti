import { APP_BOOTSTRAP, GlobalProvider, Manager } from '@metafox/framework';
import { ThemeLoader } from '@metafox/layout';
import { Theme } from '@mui/material';
import React, { useRef } from 'react';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import { HelmetProvider } from 'react-helmet-async';
import { Provider as ReduxProvider } from 'react-redux';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import {
  HistoryServiceAware,
  MainRoutes,
  ModalRoutes,
  NavigationConfirm
} from '../route';
import SeoDataAware from '../route/SeoDataAware';
import { configureStore } from '../store';
import BootstrapWrapper from './BootstrapWrapper';
import ErrorHandler from './ErrorHandler';
import SiteErrorBoundary from './SiteErrorBoundary';
import 'setimmediate';

interface Props {
  config: {
    popoverHandlers?: any;
    views?: any;
    layouts?: any;
    modals?: any;
    sagas?: any;
    routes?: any;
    reducers?: any;
    messages?: any;
    settings?: any;
    services?: any;
    themeProcessors?: ((theme: Theme) => void)[];
  };
  routerProps?: any;
  children?: React.ReactNode;
  errorFallback?: React.ElementType;
  test?: boolean;
  template?: string;
  bootstrapName?: string;
  themeId?: string;
}

export default function RootContainer({
  config,
  themeId,
  template = 'layout.masterTemplate',
  bootstrapName = APP_BOOTSTRAP,
  children
}: Props) {
  const scrollRef = useRef(undefined);

  // side loading indicator
  const mn = Manager.factory(config);

  mn.use(config.services);

  const store = configureStore({
    preloadedState: {
      intl: {
        messages: config.messages
      }
    },
    rootSagas: config.sagas,
    rootReducers: config.reducers,
    globalContext: mn
  });

  const {
    ErrorBoundary,
    DraggingContainer,
    SiteDockContainer,
    SiteFixedDockContainer,
    DialogContainer,
    IntlProvider,
    ThemeProvider,
    UserPreferenceProvider,
    LayoutProvider,
    LayoutScopeContainer,
    ScrollToTopOnMount,
    ScrollProvider,
    PageLoadingIndicator,
    ToggleGroupProvider,
    MediaPlayingProvider,
    dispatch
  } = mn;

  const Template = mn.jsxBackend.get(template) ?? React.Fragment;

  // try ci only
  if (window) {
    window['$manager'] = mn;
  }

  const basename = window.__base_name__ ?? process.env.MFOX_ROUTE_BASE_NAME;

  // useMemo: start after: 8-10ms
  // useEffect: start after: 150-300ms
  // eslint-disable-next-line react-hooks/exhaustive-deps
  React.useMemo(() => {
    dispatch({ type: bootstrapName });
    // eslint-disable-next-line
  }, []);

  const Wrapper = ({ children }) => {
    return (
      <SiteErrorBoundary>
        <ReduxProvider store={store}>
          <GlobalProvider value={mn}>
            <ScrollProvider scrollRef={scrollRef}>
              <DndProvider backend={HTML5Backend}>{children}</DndProvider>
            </ScrollProvider>
          </GlobalProvider>
        </ReduxProvider>
      </SiteErrorBoundary>
    );
  };

  return (
    <Wrapper>
      <DraggingContainer />
      <IntlProvider>
        <ThemeLoader themeId={themeId} manager={mn} />
        <UserPreferenceProvider>
          <BootstrapWrapper>
            <ErrorBoundary>
              <HelmetProvider>
                {children}
                <ThemeProvider>
                  <ToggleGroupProvider>
                    <MediaPlayingProvider>
                      <PageLoadingIndicator />
                      <BrowserRouter basename={basename}>
                        <NavigationConfirm />
                        <HistoryServiceAware />
                        <SeoDataAware />
                        <DialogContainer />
                        <ErrorHandler />
                        <ScrollToTopOnMount />
                        <LayoutScopeContainer value>
                          <Template>
                            <LayoutProvider>
                              <Routes>
                                <Route path="*" element={<MainRoutes />} />
                              </Routes>
                            </LayoutProvider>
                          </Template>
                          <ModalRoutes />
                        </LayoutScopeContainer>
                        <SiteDockContainer />
                        <SiteFixedDockContainer />
                      </BrowserRouter>
                    </MediaPlayingProvider>
                  </ToggleGroupProvider>
                </ThemeProvider>
              </HelmetProvider>
            </ErrorBoundary>
          </BootstrapWrapper>
        </UserPreferenceProvider>
      </IntlProvider>
    </Wrapper>
  );
}
