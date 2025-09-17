import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import BlocksContext from './BlocksContext';
import ContentParamsContext from './ContentParamsContext';
import LayoutContext from './LayoutContext';
import LayoutProviderContext from './LayoutProviderContext';
import PageParamAware from './PageParamsAware';
import PageParamsContext from './PageParamsContext';
import { LayoutPageStateShape } from './types';

export interface LayoutProviderProps {
  children?: React.ReactNode;
}

export default function ChildLayoutProvider({ children }: LayoutProviderProps) {
  const manager = useGlobal();
  const { constants, layoutBackend, LayoutPreviewContainer, LayoutSection } =
    manager;
  const mounted = React.useRef<boolean>(true);

  React.useEffect(
    () => () => {
      mounted.current = false;
    },
    []
  );

  const [pageState, setPageState] = React.useState<LayoutPageStateShape>({
    pageName: 'default',
    blocks: [],
    contentParams: {},
    pageParams: {},
    templateName: 'default',
    previewDevice: constants.previewDevice,
    layoutEditMode: 0,
    pageSize: layoutBackend.getPageSizeByWidth(),
    header: { elements: [] },
    footer: { elements: [] },
    content: { elements: [] }
  });

  const updateState = React.useCallback((data: any) => {
    if (mounted.current) setPageState(data);
  }, []);

  const { previewDevice, layoutEditMode, content, contentParams } = pageState;

  const url = window.document.location.href;

  if (previewDevice && !constants.isLayoutPreviewWindow && url) {
    return (
      <LayoutProviderContext.Provider value={updateState}>
        <LayoutContext.Provider value={pageState}>
          <PageParamsContext.Provider value={pageState.pageParams ?? {}}>
            <PageParamAware />
            <BlocksContext.Provider value={pageState.blocks}>
              {children}
              <LayoutPreviewContainer url={url} previewDevice={previewDevice} />
            </BlocksContext.Provider>
          </PageParamsContext.Provider>
        </LayoutContext.Provider>
      </LayoutProviderContext.Provider>
    );
  }

  return (
    <LayoutProviderContext.Provider value={updateState}>
      <LayoutContext.Provider value={pageState}>
        <BlocksContext.Provider value={pageState.blocks}>
          <PageParamsContext.Provider value={pageState.pageParams ?? {}}>
            {children}
            <ContentParamsContext.Provider value={contentParams}>
              <LayoutSection
                {...content}
                layoutEditMode={layoutEditMode}
                sectionName="content"
              />
            </ContentParamsContext.Provider>
          </PageParamsContext.Provider>
        </BlocksContext.Provider>
      </LayoutContext.Provider>
    </LayoutProviderContext.Provider>
  );
}
