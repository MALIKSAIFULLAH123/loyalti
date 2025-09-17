/**
 * @type: service
 * name: LayoutProvider
 */
import { IS_INSTALLATION, useGlobal } from '@metafox/framework';
import HelmetData from '@metafox/framework/helmet/HelmetData';
import React from 'react';
import AsMasterPageContext from './AsMasterPageContext';
import BlocksContext from './BlocksContext';
import ContentParamsContext from './ContentParamsContext';
import LayoutContext from './LayoutContext';
import LayoutProviderContext from './LayoutProviderContext';
import BlockInfoLoader from './loadable/BlockInfoLoader';
import PageParamAware from './PageParamsAware';
import PageParamsContext from './PageParamsContext';
import LayoutSection from './LayoutSection';
import { LayoutPageStateShape } from './types';

export interface LayoutProviderProps {
  children?: React.ReactNode;
}

export default function LayoutProvider({ children }: LayoutProviderProps) {
  const manager = useGlobal();
  const {
    constants,
    layoutBackend,
    jsxBackend,
    LayoutPreviewContainer,
    useIsMobile,
    use
  } = manager;
  const [pageState, setPageState] = React.useState<LayoutPageStateShape>({
    pageName: 'default',
    blocks: [],
    contentParams: undefined,
    templateName: 'default',
    previewDevice: constants.previewDevice,
    layoutEditMode: 0,
    pageSize: layoutBackend.getPageSizeByWidth(),
    header: { elements: [] },
    footer: { elements: [] },
    content: { elements: [] },
    asMasterPage: false
  });
  const EditorControl = jsxBackend.get('ui.controlCenterButton');
  const {
    previewDevice,
    header,
    footer,
    content,
    layoutEditMode,
    asMasterPage,
    contentParams
  } = pageState;

  use({ isMobile: useIsMobile() });

  const url = window.document.location.href;
  const isPreview = previewDevice && !constants.isLayoutPreviewWindow && url;
  const disableHelmet = IS_INSTALLATION;

  return (
    <AsMasterPageContext.Provider value={!!asMasterPage}>
      <BlockInfoLoader />
      <LayoutProviderContext.Provider value={setPageState}>
        <LayoutContext.Provider value={pageState}>
          <BlocksContext.Provider value={pageState.blocks}>
            <PageParamsContext.Provider value={pageState.pageParams ?? {}}>
              {children}
              <PageParamAware />
              {disableHelmet ? null : <HelmetData />}
              {isPreview ? (
                <LayoutPreviewContainer
                  url={url}
                  previewDevice={previewDevice}
                />
              ) : (
                <ContentParamsContext.Provider value={contentParams}>
                  <LayoutSection
                    {...header}
                    layoutEditMode={layoutEditMode}
                    sectionName="header"
                  />
                  <LayoutSection
                    {...content}
                    layoutEditMode={layoutEditMode}
                    sectionName="content"
                  />
                  <LayoutSection
                    {...footer}
                    layoutEditMode={layoutEditMode}
                    sectionName="footer"
                  />
                </ContentParamsContext.Provider>
              )}
              {!previewDevice || !constants.isLayoutPreviewWindow ? (
                <EditorControl />
              ) : null}
            </PageParamsContext.Provider>
          </BlocksContext.Provider>
        </LayoutContext.Provider>
      </LayoutProviderContext.Provider>
    </AsMasterPageContext.Provider>
  );
}
