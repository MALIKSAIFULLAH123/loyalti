import { useGlobal } from '@metafox/framework';
import { Page } from '@metafox/layout';
import { filterShowWhen, normalizePageName } from '@metafox/utils';
import { createElement, useEffect } from 'react';
import { useAppMenu, useResourceAction } from '../hooks';
import { PageCreatorConfig, PageParams } from '../types';

interface Params extends PageParams {
  tab: string;
  view: string;
  sort: string;
  when: string;
  search: string;
  category?: string;
  defaultTab?: string;
  appName: string;
}
interface Config<T> extends PageCreatorConfig<T> {
  readonly categoryName?: string;
  filterValuesCreator?: () => Partial<T>;
}

export default function createBrowseItemPage<T extends Params = Params>({
  appName,
  pageName,
  resourceName,
  categoryName,
  defaultTab = 'all',
  loginRequired = true,
  paramCreator
}: Config<T>) {
  function BrowsePage(props: any) {
    const {
      createPageParams,
      createContentParams,
      dispatch,
      useSessionSummary,
      getAcl,
      getSetting
    } = useGlobal();

    const acl = getAcl();
    const setting = getSetting();
    const session = useSessionSummary();
    const config = useResourceAction(appName, resourceName, 'viewAll');
    const menu = useAppMenu(appName, 'sidebarMenu');
    const canAccessTab = filterShowWhen(menu.items, {
      session,
      acl,
      setting
    }).map(x => x.tab);

    const pageParams = createPageParams<T>(
      props,
      prev => ({
        tab: prev.tab ?? defaultTab,
        appName,
        resourceName,
        view: prev.tab,
        heading: undefined,
        _pageType: 'browseItem',
        pageMetaName: normalizePageName(
          appName,
          resourceName,
          'browse',
          prev.tab ?? defaultTab
        )
      }),
      paramCreator,
      prev => {
        const namedTab = prev.tab;
        const activeTab = menu.items.find(item => item.tab === namedTab);

        return {
          heading: activeTab?.label
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
      }
    );

    const contentParams = createContentParams({
      mainListing: {
        canLoadMore: true,
        contentType: resourceName,
        title: pageParams.heading,
        dataSource: {
          apiUrl: config?.apiUrl,
          apiParams: pageParams,
          apiRules: config?.apiRules
        }
      }
    });

    useEffect(() => {
      dispatch({ type: `renderPage/${pageName}`, payload: pageParams });
    }, [dispatch, pageParams]);

    const { tab } = pageParams;

    return createElement(Page, {
      pageName,
      pageParams,
      contentParams,
      loginRequired: loginRequired && !canAccessTab.includes(tab)
    });
  }

  BrowsePage.displayName = `createBrowseItemPage(${pageName})`;

  return BrowsePage;
}
