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
    dataSource = {},
    canLoadMore: canLoadMoreMain,
    ...blockProps
  } = mainListing || {};

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

  return React.createElement(component, {
    ...props,
    dataSource: compactDataSource,
    canLoadMore,
    ...blockProps
  });
}
