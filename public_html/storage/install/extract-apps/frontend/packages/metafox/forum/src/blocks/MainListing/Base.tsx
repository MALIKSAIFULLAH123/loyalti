import { ListViewBlockProps, useGlobal } from '@metafox/framework';
import React from 'react';

export default function MainListViewBlock(props: ListViewBlockProps) {
  const {
    jsxBackend,
    usePageParams,
    compactUrl,
    useContentParams,
    compactData
  } = useGlobal();
  const component = jsxBackend.get('core.block.listview');

  const { pageParams } = usePageParams();
  const { mainListing } = useContentParams();
  const {
    dataSource,
    canLoadMore: canLoadMoreMain,
    ...blockProps
  } = mainListing;

  const apiParams = compactData(
    dataSource.apiParams,
    pageParams,
    dataSource.apiRules
  );

  const canLoadMore = props.canLoadMore ?? canLoadMoreMain;

  const apiUrl = compactUrl(dataSource.apiUrl, dataSource.apiParams);

  const compactDataSource = {
    apiUrl,
    apiParams
  };

  let emptyPage = {
    image: 'ico-comments-o',
    title: 'no_threads_found',
    description: 'search_no_threads_found_desc',
    noBlock: true,
    contentStyle: {
      sx: {
        background: '0'
      }
    }
  };

  if (dataSource.apiParams.item_type === 'forum_post') {
    emptyPage = {
      image: 'ico-comments-o',
      title: 'no_posts_found',
      description: 'search_no_threads_found_desc',
      noBlock: true,
      contentStyle: {
        sx: {
          background: '0'
        }
      }
    };
  }

  return React.createElement(component, {
    ...props,
    dataSource: compactDataSource,
    canLoadMore,
    emptyPageProps: emptyPage,
    ...blockProps
  });
}
