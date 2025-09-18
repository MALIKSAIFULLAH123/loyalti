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
import { useStoryViewContext } from '@metafox/story/hooks';
// layout
import {
  Box,
  styled,
  Typography,
  Skeleton as SkeletonDefault
} from '@mui/material';
// components
import { range, get, isEmpty, camelCase } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

const TitleSession = styled(Typography, { name: 'title' })(({ theme }) => ({
  marginBottom: theme.spacing(2),
  padding: theme.spacing(0, 2)
}));

const SkeletonSession = styled(SkeletonDefault, { name: 'SkeletonSession' })(
  ({ theme }) => ({
    margin: theme.spacing(1, 2),
    marginBottom: theme.spacing(2)
  })
);

const Session = styled(Box, { name: 'session' })(({ theme }) => ({
  marginBottom: theme.spacing(1),
  marginTop: theme.spacing(1.5)
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
  errorPage
}: ListViewBlockProps) {
  const {
    jsxBackend,
    usePageParams,
    i18n,
    useWidthBreakpoint,
    useCachedBlockEmpty,
    useSession,
    useIsMobile,
    useGetItem
  } = useGlobal();
  const isTablet = useIsMobile(true);
  const pageParams = usePageParams();

  const { user: authUser } = useSession();

  const { related_user_id } = pageParams || {};

  const { identityUserStoryActive, fire, isLastStory } = useStoryViewContext();

  const userStoryActive = useGetItem(identityUserStoryActive);

  const ItemView = jsxBackend.get(itemView);
  const ButtonAdd = jsxBackend.get('button.addCreateStory');
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);
  const currentPageInitial = 1;
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
  const initRef = React.useRef<any>(false);

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
      !isTablet &&
      isLoadMoreScroll &&
      !paging?.ended &&
      !paging?.loading &&
      !hasSCroll &&
      !paging?.dirty
    ) {
      triggerLoadmore();
    }
  }, [
    isTablet,
    paging?.loading,
    paging?.page,
    isLoadMoreScroll,
    paging?.ended,
    paging?.dirty,
    hasSCroll,
    triggerLoadmore
  ]);

  const perPage = numberOfItemsPerPage || 20;
  let limitSkeleton = numberSkeleton;

  if (!canLoadMore && displayLimit) {
    limitSkeleton = displayLimit;
  }

  const { refreshing, error, ended, initialized, dirty } = paging ?? {};
  const isLoadingLoadMoreScroll = isLoadMoreScroll && !ended;
  const showLoadSmooth = canLoadSmooth && isLoadingLoadMoreScroll;

  const data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, displayLimit || paging.ids.length);

  const listUserPaging = useGetItems(data);

  // triggerloadmore when only remaining 4 user laster
  React.useEffect(() => {
    const index = listUserPaging?.findIndex(
      item => item?.id === userStoryActive?.id
    );

    if (
      !paging?.ended &&
      !paging?.loading &&
      index !== -1 &&
      index >= listUserPaging.length - 1 - 4
    ) {
      triggerLoadmore();
    }
  }, [
    paging?.loading,
    paging?.ended,
    triggerLoadmore,
    listUserPaging,
    userStoryActive?.id
  ]);

  React.useEffect(() => {
    if (handleUpdateLastRead) {
      handleUpdateLastRead();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentPage]);

  React.useEffect(() => {
    if (!refreshing || dirty) return;

    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialized, refreshing]);

  const indexRelatedUser = listUserPaging.findIndex(
    // eslint-disable-next-line eqeqeq
    item => item.id == related_user_id
  );

  React.useEffect(() => {
    if (error || isEmpty(listUserPaging)) {
      fire({ type: 'setListUserStories', payload: [] });

      return;
    }

    fire({ type: 'setListUserStories', payload: listUserPaging });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listUserPaging.length, error]);

  // set init data context
  React.useEffect(() => {
    if (initRef.current) return;

    if (listUserPaging.length) {
      initRef.current = true;

      if (listUserPaging[indexRelatedUser]) {
        fire({
          type: 'setIdentityUserS_Active',
          payload: listUserPaging[indexRelatedUser]?._identity
        });

        return;
      }

      fire({
        type: 'setIdentityUserS_Active',
        payload: listUserPaging[0]?._identity
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listUserPaging.length, indexRelatedUser]);

  const ownerStory = listUserPaging.find(({ id }) => authUser?.id === id);

  const otherStory = [...listUserPaging];

  if (ownerStory) {
    otherStory.shift();
  }

  const handleClickItem = ({
    item,
    isOwner
  }: {
    item: any;
    isOwner?: boolean;
  }) => {
    if (isLastStory) {
      fire({ type: 'setLastStory', payload: false });
    }

    if (userStoryActive?.id === item?.id) return;

    if (isOwner) {
      fire({
        type: 'setIdentityUserS_Active',
        payload: listUserPaging[0]?._identity
      });

      return;
    }

    const userStoryIndex = listUserPaging.findIndex(
      user => user?.id === item?.id
    );

    if (listUserPaging?.[userStoryIndex]) {
      fire({
        type: 'setIdentityUserS_Active',
        payload: listUserPaging[userStoryIndex]?._identity
      });
    }
  };

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
          <Session>
            <TitleSession variant="h5">
              {i18n.formatMessage({ id: 'my_story' })}
            </TitleSession>
            {ButtonAdd && <ButtonAdd />}
          </Session>
          <Session>
            <TitleSession variant="h5" mb={0}>
              {i18n.formatMessage({ id: 'all_stories' })}
            </TitleSession>
            {React.createElement(NoResultsBlock, { ...emptyPageProps })}
          </Session>
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
            <SkeletonSession height={20} width={'25%'} />
            {range(0, 2).map(index => (
              <Skeleton itemProps={itemProps} key={index.toString()} />
            ))}
          </Session>
          <Session>
            <SkeletonSession height={20} width={'25%'} />
            {range(0, limitSkeleton).map(index => (
              <Skeleton itemProps={itemProps} key={index.toString()} />
            ))}
          </Session>
        </BlockContent>
      </Block>
    );
  }

  if (!data.length) return;

  const NoResultsBlock = jsxBackend.get(emptyPage);

  if (!NoResultsBlock) return null;

  const { noBlock } = emptyPageProps || {};

  if (noBlock) {
    return <NoResultsBlock {...emptyPageProps} />;
  }

  return (
    <Block>
      <BlockContent>
        <Session data-testid={camelCase('my_story')}>
          <TitleSession variant="h5">
            {i18n.formatMessage({ id: 'my_story' })}
          </TitleSession>
          {ButtonAdd && <ButtonAdd />}
          {ownerStory && (
            <Box
              onClick={() =>
                handleClickItem({ item: ownerStory, isOwner: true })
              }
            >
              <ItemView identity={ownerStory._identity} itemProps={itemProps} />
            </Box>
          )}
        </Session>
        <Session data-testid={camelCase('all_stories')}>
          <TitleSession variant="h5" mb={0}>
            {i18n.formatMessage({ id: 'all_stories' })}
          </TitleSession>
          {isEmpty(otherStory) && !showLoadSmooth
            ? React.createElement(NoResultsBlock, { ...emptyPageProps })
            : otherStory.map((user, index) => (
                <Box
                  key={index.toString()}
                  onClick={() => handleClickItem({ item: user })}
                >
                  <ItemView identity={user._identity} itemProps={itemProps} />
                </Box>
              ))}
        </Session>
        {showLoadSmooth && listUserPaging ? (
          <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
            {range(6).map(index => (
              <Skeleton key={index.toString()} />
            ))}
          </Box>
        ) : null}
      </BlockContent>
    </Block>
  );
}

export default withPagination(ListView);
