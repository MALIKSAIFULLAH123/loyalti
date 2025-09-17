import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  useScrollEnd,
  useHasScroll,
  useGetItems,
  withPagination
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
// layout
import {
  Box,
  styled,
  Typography,
  Skeleton as SkeletonDefault
} from '@mui/material';
// components
import { range, get, groupBy } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import moment from 'moment';
import { dateDiffInDays } from '@metafox/utils';
import MoreUserName from './MoreUserName';

const toMonth = (number: number) => {
  return number > 12 ? number - 12 : number;
};

const isEndedLoadWithMonth = (data: any, month: string) => {
  const keys = Object.keys(data);
  const indexMonth = keys.indexOf(month);

  return !!keys[indexMonth + 1];
};

const Session = styled(Box, { name: 'sessionBirthday' })(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  marginTop: theme.spacing(2),
  borderRadius: theme.spacing(2),
  padding: theme.spacing(2)
}));

function ListView({
  blockId,
  title,
  itemView,
  itemProps = {},
  gridItemProps = {},
  displayLimit: displayLimitProp = 4,
  displayRowsLimit,
  pagingId,
  canLoadMore,
  handleUpdateLastRead,
  canLoadSmooth,
  loadMore,
  numberOfItemsPerPage,
  emptyPage = 'core.block.no_content',
  emptyPageProps,
  isLoadMoreScroll,
  isLoadMorePagination,
  errorPage
}: ListViewBlockProps) {
  const {
    jsxBackend,
    usePageParams,
    i18n,
    useWidthBreakpoint,
    useCachedBlockEmpty,
    getSetting
  } = useGlobal();
  const pageParams = usePageParams();
  const dayUpcoming: number = getSetting('friend.days_to_check_for_birthday');

  const monthCurrent = moment(new Date()).add(dayUpcoming, 'days').month() + 1;
  const { page } = pageParams;
  const ItemView = jsxBackend.get(itemView);
  const ItemViewAvatar = jsxBackend.get('friend.itemView.birthdayWidthAvatar');
  const SkeletonAvatar = jsxBackend.get(
    'friend.itemView.birthdayWidthAvatar.skeleton'
  );
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);
  const currentPageInitial = isLoadMorePagination ? parseInt(page || 1, 10) : 1;
  const [hasSCroll, checkSCrollExist] = useHasScroll(true);
  const mediaBreakpoint: string = useWidthBreakpoint();
  // number skeleton loadmore is 2 line of grid
  const gridNumberPerRow =
    12 / parseInt(get(gridItemProps, mediaBreakpoint) || 12, 10);
  const displayLimit = displayRowsLimit
    ? displayRowsLimit * gridNumberPerRow
    : displayLimitProp;
  const numberSkeleton = gridNumberPerRow * 2;
  const [isCachedEmpty, setCached] = useCachedBlockEmpty(blockId);

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
  }, [pagingId]);

  React.useEffect(() => {
    if (checkSCrollExist) {
      checkSCrollExist();
    }
  }, [paging?.page, checkSCrollExist]);

  React.useEffect(() => {
    // triggerloadmore is container not have scroll bar
    if (
      isLoadMoreScroll &&
      !paging?.ended &&
      !paging?.loading &&
      !hasSCroll &&
      !paging?.dirty
    ) {
      triggerLoadmore();
    }
  }, [
    paging?.loading,
    paging?.page,
    isLoadMoreScroll,
    paging?.ended,
    paging?.dirty,
    hasSCroll,
    triggerLoadmore
  ]);

  const perPage = numberOfItemsPerPage || 20;
  let limitSkeleton = isLoadMorePagination ? perPage : numberSkeleton;

  if (!canLoadMore && displayLimit) {
    limitSkeleton = displayLimit;
  }

  const { loading, refreshing, error, ended, initialized, pagesOffset, dirty } =
    paging ?? {};
  const isLoadingPagination = isLoadMorePagination && loading;
  const isLoadingLoadMoreScroll = isLoadMoreScroll && !ended;

  const showLoadSmooth =
    canLoadSmooth && (isLoadingLoadMoreScroll || isLoadingPagination);

  let data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, displayLimit || paging.ids.length);

  const dataUser = useGetItems(data);

  if (isLoadMorePagination) {
    data = paging.pages[currentPage]?.ids || [];
  }

  React.useEffect(() => {
    if (handleUpdateLastRead) {
      handleUpdateLastRead();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentPage]);

  React.useEffect(() => {
    if (!isLoadMorePagination) return;

    const current_page = parseInt(
      pageParams?.page || pagesOffset?.current_page,
      10
    );
    setCurrentPage(current_page);
    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isLoadMorePagination, pageParams?.page, pagesOffset?.current_page]);

  React.useEffect(() => {
    if (!refreshing || dirty) return;

    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialized, refreshing]);

  if (!ItemView) return null;

  if (!gridItemProps.xs) {
    gridItemProps.xs = 12;
  }

  if (error) {
    if (errorPage === 'hide') return null;

    const message =
      get(error, 'response.data.error') || get(error, 'response.data.message');

    const errorName =
      get(error, 'response.status') === 403
        ? 'core.block.error403'
        : 'core.block.error404';
    const ErrorBlock = jsxBackend.get(errorName);

    if (errorPage === 'default') {
      return (
        <Block>
          <BlockHeader title={title} data={data} />
          <BlockContent>
            <ErrorBlock title={message} />
          </BlockContent>
        </Block>
      );
    }

    return <ErrorBlock title={message} />;
  }

  if ((!data.length && (ended || isCachedEmpty)) || error) {
    if (!isCachedEmpty) {
      setCached(true);
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
        <BlockContent {...contentStyle}>
          {React.createElement(NoResultsBlock, { ...emptyPageProps })}
        </BlockContent>
      </Block>
    );
  }

  // clear cachedEmpty block
  if (isCachedEmpty) {
    setCached(false);
  }

  if (!initialized) {
    if (!Skeleton) {
      return <div>{i18n.formatMessage({ id: 'loading_dots' })}</div>;
    }

    return (
      <Block>
        <BlockContent>
          <Session>
            {range(0, limitSkeleton).map(index => (
              <Skeleton
                testid="loadingIndicator"
                itemProps={itemProps}
                key={index.toString()}
              />
            ))}
          </Session>
        </BlockContent>
      </Block>
    );
  }

  if (!data.length) return;

  const result = groupBy(dataUser, ({ month, birthday, birthday_format }) => {
    const endDate = moment(birthday, birthday_format);
    const duration = dateDiffInDays(endDate);
    const endMonth = endDate.month() + 1;

    if (duration === 0) return 'today';

    if (duration <= dayUpcoming && duration > 0) return 'upcoming';

    if (endMonth === monthCurrent && duration < 0)
      return 'recentOfCurrentMonth';

    return month;
  });

  return (
    <Block>
      <BlockContent>
        {result?.today && (
          <Session>
            <Typography variant="h3">
              {i18n.formatMessage({ id: 'today_birthdays' })}
            </Typography>
            {result?.today.map((user, index) => (
              <ItemView
                index={index}
                identity={user._identity}
                itemProps={itemProps}
                key={index.toString()}
              />
            ))}
          </Session>
        )}
        {result?.upcoming && (
          <Session>
            <Typography variant="h3">
              {i18n.formatMessage({ id: 'upcoming_birthdays' })}
            </Typography>
            {result?.upcoming.map((user, index) => (
              <ItemView
                index={index}
                identity={user._identity}
                itemProps={itemProps}
                key={index.toString()}
              />
            ))}
          </Session>
        )}
        <Box>
          {range(monthCurrent, monthCurrent + 12).map(
            index =>
              result[`${toMonth(index)}`] &&
              (isEndedLoadWithMonth(result, `${toMonth(index)}`) || ended) && (
                <Session key={index.toString()}>
                  <Typography variant="h3">
                    {moment()
                      .month(toMonth(index) - 1)
                      .format('MMMM')}
                  </Typography>
                  <MoreUserName data={result[`${toMonth(index)}`]} />
                  <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
                    {result[`${toMonth(index)}`].map((user, id) => (
                      <ItemViewAvatar
                        identity={user._identity}
                        itemProps={itemProps}
                        key={id.toString()}
                      />
                    ))}
                  </Box>
                </Session>
              )
          )}
          {result?.recentOfCurrentMonth && (
            <Session>
              <Typography variant="h3">
                {moment()
                  .month(monthCurrent - 1)
                  .format('MMMM')}
              </Typography>
              <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
                {result.recentOfCurrentMonth.map((user, id) => (
                  <ItemViewAvatar
                    identity={user._identity}
                    itemProps={itemProps}
                    key={id.toString()}
                  />
                ))}
                {showLoadSmooth
                  ? range(3).map(indexLoad => (
                      <SkeletonAvatar key={indexLoad.toString()} />
                    ))
                  : null}
              </Box>
            </Session>
          )}
        </Box>
        {showLoadSmooth && result ? (
          <Session>
            <SkeletonDefault width={'25%'} />
            <SkeletonDefault width={'50%'} />
            <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
              {range(6).map(index => (
                <SkeletonAvatar key={index.toString()} />
              ))}
            </Box>
          </Session>
        ) : null}
      </BlockContent>
    </Block>
  );
}

export default withPagination(ListView);
