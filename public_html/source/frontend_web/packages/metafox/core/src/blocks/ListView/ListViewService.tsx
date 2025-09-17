/**
 * @type: service
 * name: ListView
 */

import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  useScrollEnd,
  withPagination
} from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockContext,
  BlockHeader
} from '@metafox/layout';
import { Grid } from '@mui/material';
import { isArray, range } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import { getItemsPerRowOnMediaScreen } from '@metafox/layout/utils';

function ListView({
  itemView,
  itemProps = {},
  blockProps,
  gridItemProps = {},
  gridContainerProps = { spacing: 2 },
  displayLimit: displayLimitProp,
  pagingId,
  canLoadMore,
  canLoadSmooth,
  loadMore,
  numberOfItemsPerPage,
  emptyPage = 'core.block.no_results',
  emptyPageProps,
  limitItemsLoadSmooth,
  title,
  errorPage,
  handleActionItem,
  displayRowsLimit
}: ListViewBlockProps) {
  const { jsxBackend, i18n, useWidthBreakpoint, useTheme } = useGlobal();
  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();
  const [currentPage, setCurrentPage] = React.useState<number>(
    paging?.page || 1
  );

  useScrollEnd(
    canLoadMore && currentPage <= paging.page
      ? () => setCurrentPage(prev => prev + 1)
      : undefined
  );
  const perPage = numberOfItemsPerPage || 20;
  const mediaBreakpoint: string = useWidthBreakpoint();
  const theme = useTheme();
  const keysBreakpoint = [...theme.breakpoints.keys];

  const gridNumberPerRow = displayRowsLimit
    ? getItemsPerRowOnMediaScreen(
        mediaBreakpoint,
        gridItemProps,
        keysBreakpoint
      )
    : 0;

  const displayLimit = gridNumberPerRow
    ? displayRowsLimit * gridNumberPerRow
    : displayLimitProp;
  const limit = displayLimit || 4;

  const { refreshing, error, ended, initialized, noResultProps } = paging ?? {};
  const showLoadSmooth = canLoadSmooth && !ended;

  const data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, displayLimit || paging.ids.length);

  React.useEffect(() => {
    if (!refreshing) return;

    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [refreshing]);

  if (!ItemView) return null;

  if (error) {
    if (errorPage === 'hide') return null;

    return <ErrorBoundary error={error} />;
  }

  if (!gridItemProps.xs) {
    gridItemProps.xs = 12;
  }

  if (!ItemView) return null;

  if (!initialized) {
    if (!Skeleton) {
      return <div>{i18n.formatMessage({ id: 'loading_dots' })}</div>;
    }

    return (
      <BlockContext.Provider
        value={{ itemProps, gridContainerProps, blockProps }}
      >
        <Block>
          <BlockHeader title={title} />
          <BlockContent>
            <Grid container {...gridContainerProps}>
              {range(0, limit).map(index => (
                <Skeleton
                  testid="loadingIndicator"
                  wrapAs={Grid}
                  wrapProps={gridItemProps}
                  itemProps={itemProps}
                  key={index.toString()}
                />
              ))}
            </Grid>
          </BlockContent>
        </Block>
      </BlockContext.Provider>
    );
  }

  if (!data.length && ended) {
    if (emptyPage === 'hide') return null;

    if (typeof emptyPage !== 'string') return emptyPage;

    const NoResultsBlock = jsxBackend.get(emptyPage);
    const emptyProps = { ...emptyPageProps, ...noResultProps };

    return <NoResultsBlock {...emptyProps} />;
  }

  return (
    <BlockContext.Provider
      value={{ itemProps, gridContainerProps, blockProps }}
    >
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Grid container {...gridContainerProps}>
            {isArray(data) &&
              data.map(id => (
                <ItemView
                  identity={id}
                  itemProps={itemProps}
                  key={id.toString()}
                  wrapAs={Grid}
                  wrapProps={gridItemProps}
                  handleActionItem={handleActionItem}
                />
              ))}
            {showLoadSmooth
              ? range(0, limitItemsLoadSmooth || limit).map(index => (
                  <Skeleton
                    testid="loadingIndicator"
                    wrapAs={Grid}
                    wrapProps={gridItemProps}
                    itemProps={itemProps}
                    key={index.toString()}
                  />
                ))
              : null}
          </Grid>
        </BlockContent>
      </Block>
    </BlockContext.Provider>
  );
}

export default withPagination(ListView);
