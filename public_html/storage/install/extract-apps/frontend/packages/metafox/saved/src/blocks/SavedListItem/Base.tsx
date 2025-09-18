import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import React from 'react';
import { APP_SAVED, RESOURCE_SAVED_LIST } from '@metafox/saved/constant';
import { compactData, compactUrl } from '@metafox/utils';
import { getPagingIdListSaved } from '@metafox/saved/utils';

export type Props = BlockViewProps;

const Base = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  emptyPage,
  emptyPageProps,
  ...rest
}: Props) => {
  const {
    jsxBackend,
    usePageParams,
    useContentParams,
    useIsMobile,
    dispatch,
    useGetItem
  } = useGlobal();
  const isMobile = useIsMobile(true);
  const dataSource = useResourceAction(
    APP_SAVED,
    RESOURCE_SAVED_LIST,
    'viewItem'
  );

  const pageParams = usePageParams();
  const { mainListing } = useContentParams();
  const { collection_id } = pageParams || {};
  const collection = useGetItem(
    `${APP_SAVED}.entities.${RESOURCE_SAVED_LIST}.${collection_id}`
  );
  const pagingId = getPagingIdListSaved(pageParams);

  React.useEffect(() => {
    if (isMobile) {
      dispatch({ type: 'savedList/fetchItemListMobile' });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isMobile]);

  const ListView = jsxBackend.get('core.block.listview');

  const EmptyPage = jsxBackend.get('core.block.no_results');

  if (!dataSource) return React.createElement(EmptyPage);

  return (
    <ListView
      {...rest}
      title={collection?.name || mainListing?.title || title}
      emptyPage={emptyPage}
      emptyPageProps={emptyPageProps}
      canLoadMore
      dataSource={{
        apiUrl: compactUrl(dataSource?.apiUrl, {
          id: pageParams.collection_id
        }),
        apiParams: compactData(dataSource.apiParams, pageParams)
      }}
      clearDataOnUnMount
      gridVariant={gridVariant}
      gridLayout={gridLayout}
      itemLayout={itemLayout}
      itemView={itemView}
      blockLayout={rest.blockLayout}
      pagingId={pagingId}
      key={pagingId}
    />
  );
};

export default Base;
