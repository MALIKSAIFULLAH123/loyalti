import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  useScrollEnd
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { getItemsPerRowOnMediaScreen } from '@metafox/layout/utils';
// layout
import { Grid, Box } from '@mui/material';
// components
import { isArray, range, get } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import { LoadMoreListingButton } from '@metafox/ui';
import { useInView } from 'react-intersection-observer';

export default function ListViewBase(props: ListViewBlockProps) {
  const {
    blockId,
    title,
    itemView,
    itemProps = {},
    gridItemProps = {},
    gridContainerProps = { spacing: 2 },
    displayLimit: displayLimitProp = 4,
    displayRowsLimit,
    pagingId,
    canLoadMore,
    buttonMessageLoadmore = 'view_more',
    canLoadSmooth,
    loadMore,
    numberOfItemsPerPage,
    emptyPage = 'core.block.no_content',
    emptyPageProps,
    loadMoreTypeProp,
    isLoadMoreScroll,
    isLoadMoreButton,
    errorPage,
    errorPageProps,
    handleActionItem,
    cachedEmpty
  } = props;

  const {
    jsxBackend,
    i18n,
    useWidthBreakpoint,
    useCachedBlockEmpty,
    useTheme
  } = useGlobal();
  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);
  const currentPageInitial = 1;
  const mediaBreakpoint: string = useWidthBreakpoint();
  const theme = useTheme();
  const keysBreakpoint = [...theme.breakpoints.keys];

  const gridNumberPerRow = getItemsPerRowOnMediaScreen(
    mediaBreakpoint,
    gridItemProps,
    keysBreakpoint
  );

  const displayLimit = displayRowsLimit
    ? displayRowsLimit * gridNumberPerRow
    : displayLimitProp;
  const numberSkeleton = gridNumberPerRow * 2;
  const [isCachedEmpty, setCachedEmpty] = useCachedBlockEmpty(
    `${pagingId}_${blockId}`
  );
  const [refCheckLessItem, inViewLessItem] = useInView({
    threshold: 0
  });

  const [currentPage, setCurrentPage] =
    React.useState<number>(currentPageInitial);
  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();
  const callbackScrollEnd = React.useCallback(() => {
    if (isLoadMoreScroll && currentPage <= paging.page) {
      setCurrentPage(prev => prev + 1);
    }
  }, [isLoadMoreScroll, currentPage, paging.page]);

  useScrollEnd(callbackScrollEnd);

  const triggerLoadmore = React.useCallback(() => {
    loadMore();
    setCurrentPage(prev => prev + 1);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId, loadMore]);
  const { loading, refreshing, error, ended, initialized, dirty } =
    paging ?? {};

  React.useEffect(() => {
    if (!isLoadMoreScroll || !inViewLessItem || loading || ended) return;

    triggerLoadmore();
  }, [inViewLessItem, loading, triggerLoadmore, isLoadMoreScroll, ended]);

  const perPage = numberOfItemsPerPage || 20;
  let limitSkeleton = numberSkeleton;

  if (!canLoadMore && displayLimit) {
    limitSkeleton = displayLimit;
  }

  const isLoadingLoadMoreScroll = isLoadMoreScroll && !ended;
  const isLoadingLoadMoreButton = isLoadMoreButton && loading;
  const showLoadSmooth =
    canLoadSmooth && (isLoadingLoadMoreScroll || isLoadingLoadMoreButton);
  const checkLessItem = !ended && isLoadingLoadMoreScroll;
  const data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, displayLimit || paging.ids.length);

  React.useEffect(() => {
    if (!refreshing || dirty) return;

    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialized, refreshing]);

  React.useEffect(() => {
    // clear cachedEmpty block
    if (isCachedEmpty && data.length) {
      setCachedEmpty(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isCachedEmpty, data.length]);

  if (!ItemView) return null;

  if (!gridItemProps.xs) {
    gridItemProps.xs = 12;
  }

  if (error) {
    if (errorPage === 'hide') return null;

    const message =
      get(error, 'response.data.error') ||
      get(error, 'response.data.message') ||
      get(error, 'response.data.meta.title');

    const content = get(error, 'response.data.meta.description');

    const errorName =
      get(error, 'response.status') === 403
        ? 'core.block.error403'
        : 'core.block.error404';
    const ErrorBlock = jsxBackend.get(errorName);
    const { hideContent = false } = errorPageProps || {};

    if (errorPage === 'default') {
      return (
        <Block>
          <BlockHeader title={title} data={data} />
          {hideContent ? null : (
            <BlockContent>
              <ErrorBlock title={message} content={content} />
            </BlockContent>
          )}
        </Block>
      );
    }

    return <ErrorBlock title={message} content={content} />;
  }

  if ((!data.length && (ended || (cachedEmpty && isCachedEmpty))) || error) {
    if (!isCachedEmpty) {
      setCachedEmpty(true);
    }

    if (emptyPage === 'hide') return null;

    if (typeof emptyPage !== 'string') return emptyPage;

    const NoResultsBlock = jsxBackend.get(emptyPage);

    if (!NoResultsBlock) return null;

    const { noBlock, contentStyle } = emptyPageProps || {};

    if (noBlock) {
      return <NoResultsBlock {...emptyPageProps} />;
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent {...contentStyle}>
          {React.createElement(NoResultsBlock, { ...emptyPageProps })}
        </BlockContent>
      </Block>
    );
  }

  if (!initialized) {
    if (!Skeleton) {
      return (
        <div data-testid="loadingIndicator">
          {i18n.formatMessage({ id: 'loading_dots' })}
        </div>
      );
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Grid
            container
            sx={{ '&:empty': { display: 'none' } }}
            {...gridContainerProps}
          >
            {range(0, limitSkeleton).map(index => (
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
    );
  }

  return (
    <Block>
      <BlockHeader title={title} data={data} />
      <BlockContent>
        <Grid container {...gridContainerProps}>
          {isArray(data) &&
            data.map((id, index) => (
              <ItemView
                index={index}
                identity={id}
                itemProps={itemProps}
                key={id.toString()}
                wrapAs={Grid}
                wrapProps={gridItemProps}
                pagingId={pagingId}
                handleActionItem={handleActionItem}
              />
            ))}
          {checkLessItem ? (
            <Box
              ref={refCheckLessItem}
              sx={{ width: '0 !important', height: '0 !important' }}
            />
          ) : null}
          {showLoadSmooth && Skeleton ? (
            <>
              {range(0, limitSkeleton).map(index => (
                <Skeleton
                  wrapAs={Grid}
                  wrapProps={gridItemProps}
                  itemProps={itemProps}
                  key={index.toString()}
                />
              ))}
            </>
          ) : null}
        </Grid>
        {isLoadMoreButton &&
        ((!ended && !loading) || currentPage < paging?.page) ? (
          <LoadMoreListingButton
            handleClick={triggerLoadmore}
            message={buttonMessageLoadmore}
            loadMoreTypeProp={loadMoreTypeProp}
          />
        ) : null}
      </BlockContent>
    </Block>
  );
}
