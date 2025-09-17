// types
import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGetItem,
  useGlobal,
  useScrollEnd,
  useSession
} from '@metafox/framework';
// layout
import {
  Block,
  BlockContent,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
// components
import { GridItem } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, CircularProgress, Grid, useTheme } from '@mui/material';
// utils
import { get, isArray, range, isNumber } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import CustomItem from './CustomItem';
import StackGrid from 'react-stack-grid';

export interface TItemBase {
  id: number;
  resource_name?: string;
  module_name?: string;
}

export interface Props extends ListViewBlockProps {
  numColumns?: number;
  margin?: number;
}

const wrapProps = {};

export default function PinListView({
  title,
  numColumns = 4,
  limitItemsLoadSmooth = 4,
  numberOfItemsPerPage,
  displayLimit,
  pagingId,
  itemProps = {},
  gridItemProps = {},
  gridContainerProps,
  itemView,
  canLoadMore,
  canLoadSmooth = true,
  loadMore,
  emptyPage = 'no_content',
  emptyPageProps,
  startItemView
}: Props) {
  const { jsxBackend } = useGlobal();
  const { loggedIn, user: authUser } = useSession();
  const pageParams = usePageParams();
  let identity = pageParams?.identity;
  const theme = useTheme();

  if (pageParams?.profile_page && pageParams?.album_id) {
    identity = `photo.entities.photo_album.${pageParams.album_id}`;
  }

  const item = useGetItem(identity);

  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);

  // check show startItemView
  let startItemViews = [];

  if (startItemView) {
    const isAuthUser =
      authUser?.id &&
      pageParams?.id &&
      authUser?.id === parseInt(pageParams.id);

    const condition = { pageParams, authUser, loggedIn, isAuthUser, item };

    startItemViews = filterShowWhen(startItemView, condition).map(
      ({ as: c, ...props }, index) => ({
        component: c,
        props: {
          key: index.toString(),
          ...props
        }
      })
    );
  }

  const [currentPage, setCurrentPage] = React.useState<number>(1);
  const perPage = numberOfItemsPerPage || 20;
  const limit = displayLimit || 4;
  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  useScrollEnd(
    canLoadMore ? () => setCurrentPage(prev => prev + 1) : undefined
  );

  if (!paging?.ids) paging.ids = [];

  const data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, limit || 4);

  const dataRender = [...startItemViews, ...data];

  const { loading, refreshing, error, ended, initialized } = paging ?? {};
  const showLoadSmooth = canLoadSmooth && !ended;

  if (!itemProps) {
    itemProps = { xs: 12 };
  }

  React.useEffect(() => {
    if (
      (initialized && canLoadMore && !loading && loadMore && !ended) ||
      refreshing
    ) {
      loadMore();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialized, refreshing]);

  if (!ItemView) return null;

  if (error) {
    const message =
      get(error, 'response.data.error') || get(error, 'response.data.message');

    const errorName =
      get(error, 'response.status') === 403
        ? 'core.block.error403'
        : 'core.block.error404';
    const ErrorBlock = jsxBackend.get(errorName);

    return <ErrorBlock title={message} />;
  }

  const spacing = gridContainerProps?.spacing;
  const gutter = isNumber(spacing) ? (theme.gridPoint || 8) * spacing : 0;
  const colWid = `${Math.floor((100 / numColumns) * 10) / 10}%`;

  if (!initialized) {
    if (!Skeleton) {
      return (
        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}
        >
          <CircularProgress size={30} />
        </Box>
      );
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <StackGrid
            columnWidth={colWid}
            gutterWidth={gutter}
            gutterHeight={gutter}
            monitorImagesLoaded
          >
            {range(1, limit).map(index => (
              <Skeleton
                wrapAs={Grid}
                wrapProps={gridItemProps}
                itemProps={itemProps}
                key={index.toString()}
              />
            ))}
          </StackGrid>
        </BlockContent>
      </Block>
    );
  }

  if (!data.length && ended) {
    if (emptyPage === 'hide') return null;

    const NoResultsBlock = jsxBackend.get(emptyPage);

    const { noBlock } = emptyPageProps || {};

    if (noBlock) {
      return <NoResultsBlock {...emptyPageProps} />;
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <NoResultsBlock {...emptyPageProps} />
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Box sx={{ '& article': { opacity: '1 !important' } }}>
          <StackGrid
            columnWidth={colWid}
            gutterWidth={gutter}
            gutterHeight={gutter}
            monitorImagesLoaded
            itemComponent={'article'}
          >
            {isArray(dataRender) &&
              dataRender.map((id, index) =>
                id?.component ? (
                  <CustomItem key={`k${index}`} order={index} item={id} />
                ) : (
                  <ItemView
                    identity={id}
                    key={`k${index}`}
                    order={index}
                    wrapAs={GridItem}
                    wrapProps={wrapProps}
                    itemProps={itemProps}
                  />
                )
              )}
            {showLoadSmooth
              ? range(1, limitItemsLoadSmooth || limit).map(index => (
                  <Skeleton
                    wrapAs={Grid}
                    wrapProps={gridItemProps}
                    itemProps={itemProps}
                    key={index.toString()}
                  />
                ))
              : null}
          </StackGrid>
        </Box>
      </BlockContent>
    </Block>
  );
}
