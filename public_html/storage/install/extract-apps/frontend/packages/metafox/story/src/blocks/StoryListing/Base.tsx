import {
  PAGINATION,
  useGlobal,
  useResourceAction,
  getPagingSelector,
  GlobalState,
  PagingState,
  initPagingState,
  PAGINATION_CLEAR,
  useAbortControl,
  PAGINATION_INIT
} from '@metafox/framework';
import { OnStickerClick } from '@metafox/sticker';
import { styled } from '@mui/material/styles';
import React from 'react';
import Slider from './Slider';
import qs from 'query-string';
import { useSelector } from 'react-redux';
import { Block, BlockContent } from '@metafox/layout';
import { APP_STORY, RESOURCE_STORY } from '@metafox/story/constants';
import AddItemCard from './AddItemCard';
import { isEmpty, omit, range } from 'lodash';
import { ViewFeedStatus } from '@metafox/story/types';
import CreateStoryBlock from './CreateStoryBlock';

const ContainerList = styled('div', { slot: 'ContainerList' })(({ theme }) => ({
  width: '100%',
  margin: theme.spacing(0),
  padding: theme.spacing(0),
  '& .slick-track': {
    display: 'flex',
    flexWrap: 'nowrap',
    width: '100% !important',
    flexDirection: 'row',
    margin: 0
  },
  '& .slick-initialized .slick-slide': {
    display: 'flex',
    '& > div': {
      width: '100%',
      height: '100%'
    }
  },
  '& .slick-list': {
    cursor: 'pointer'
  },
  '& .slick-next': {
    display: 'flex!important',
    alignItems: 'center',
    width: '42px',
    height: '42px',
    right: '12px',
    fontSize: '22px',
    justifyContent: 'center',
    borderLeft: '1px solid',
    borderRight: '1px solid',
    borderRadius: '50%',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    }
  },
  '& .slick-prev': {
    display: 'flex!important',
    alignItems: 'center',
    width: '42px',
    height: '42px',
    left: '12px',
    fontSize: '22px',
    justifyContent: 'center',
    borderLeft: '1px solid',
    borderRight: '1px solid',
    borderRadius: '50%',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    }
  }
}));

interface Props {
  multiple?: boolean;
  onStickerClick?: OnStickerClick;
}

export default function StoryListing(props: Props) {
  const {
    i18n,
    dispatch,
    jsxBackend,
    useGetItems,
    compactData,
    usePageParams,
    getAcl,
    useIsMobile,
    getSetting,
    useFetchDetail
  } = useGlobal();
  const [userSugestions, setUserSugestions] = React.useState([]);

  const isMobile = useIsMobile();
  const viewStoryStatus = getSetting('story.home_page_style');

  const createAcl = getAcl('story.story.create');
  const viewAcl = getAcl('story.story.view');
  const moderateAcl = getAcl('story.story.moderate');
  const canViewStory = moderateAcl || viewAcl;

  const dataSource = useResourceAction(
    APP_STORY,
    RESOURCE_STORY,
    'getFriendSuggest'
  );

  const [dataUsers, isLoadingSuggestion, errorUsers] = useFetchDetail({
    dataSource,
    pageParams: { limit: 10 },
    normalize: true
  });

  const initializedSuggestion = !isLoadingSuggestion || isEmpty(dataSource);

  React.useEffect(() => {
    if (isLoadingSuggestion) return;

    setUserSugestions(errorUsers || isEmpty(dataUsers) ? [] : dataUsers);
  }, [dataUsers, isLoadingSuggestion, errorUsers]);

  const removeUser = (user_id: any) => {
    if (!user_id) return;

    const data = userSugestions.filter(user => user.id !== user_id);
    setUserSugestions(data);
    dispatch({
      type: 'story/sugestion/hideUser',
      payload: { user_id }
    });
  };

  const isShowAvatar = React.useMemo(() => {
    return viewStoryStatus === ViewFeedStatus.Avatar;
  }, [viewStoryStatus]);

  const UserSugestionCard = React.useMemo(() => {
    if (isShowAvatar) {
      return jsxBackend.get('story.itemView.userSugestionAvatarCard');
    }

    return jsxBackend.get('story.itemView.userSugestionCard');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isShowAvatar]);

  const ItemView = React.useMemo(() => {
    if (isShowAvatar) {
      return jsxBackend.get('story.itemView.storyAvatarCard');
    }

    return jsxBackend.get('story.itemView.storyCard');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isShowAvatar]);

  const Skeleton = React.useMemo(() => {
    if (isShowAvatar) {
      return jsxBackend.get('story.itemView.storyAvatarCard.skeleton');
    }

    return jsxBackend.get('story.itemView.storyCard.skeleton');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isShowAvatar]);

  const pageParams = usePageParams();

  const config = useResourceAction(APP_STORY, RESOURCE_STORY, 'viewAll');
  const apiParams = compactData(
    config?.apiParams,
    pageParams,
    config?.apiRules
  );

  const pagingId = `${config?.apiUrl}?${qs.stringify(
    omit(apiParams, ['page'])
  )}`;
  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  const { initialized, loading, error } = paging ?? {};

  const listUserStories: any[] = useGetItems(paging.ids);

  const abortId = useAbortControl(pagingId);

  const isShowUsers = React.useMemo(() => {
    if (errorUsers || !userSugestions?.length) return false;

    return paging?.ids?.length < 3;
  }, [errorUsers, userSugestions?.length, paging?.ids?.length]);

  React.useEffect(() => {
    loadMore(PAGINATION_INIT);

    return () => {
      dispatch({
        type: PAGINATION_CLEAR,
        payload: { pagingId },
        meta: { abortId }
      });
    };

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId, abortId]);

  const loadMore = React.useCallback(
    (type: string = PAGINATION) => {
      if (isShowUsers) return;

      dispatch({
        type,
        payload: {
          apiUrl: config?.apiUrl,
          apiParams,
          pagingId,
          canLoadMore: true,
          numberOfItemsPerPage: 15
        },
        meta: { abortId }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [apiParams, config, pagingId, abortId, isShowUsers]
  );

  if (
    error ||
    (initialized &&
      initializedSuggestion &&
      isEmpty(listUserStories) &&
      isEmpty(userSugestions))
  ) {
    return <CreateStoryBlock />;
  }

  if (!initialized || !initializedSuggestion) {
    if (!Skeleton) {
      return <div>{i18n.formatMessage({ id: 'loading_dots' })}</div>;
    }

    return (
      <Block>
        <BlockContent>
          <ContainerList>
            <Slider showArrow={false} loadMore={loadMore}>
              {range(0, isShowAvatar ? 15 : 6).map(index => (
                <Skeleton key={index.toString()} />
              ))}
            </Slider>
          </ContainerList>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockContent>
        <ContainerList>
          <Slider
            loadMore={loadMore}
            loadMoreLoading={loading}
            ended={paging?.ended}
            isMobile={isMobile}
            isShowAvatar={isShowAvatar}
            total={
              isShowUsers
                ? listUserStories.length + userSugestions?.length
                : listUserStories.length
            }
            key="story-slider"
          >
            <AddItemCard
              title={isShowAvatar ? 'my_story' : 'create_story'}
              canCreate={createAcl}
            />
            {canViewStory
              ? listUserStories.map(item => (
                  <ItemView key={item?.id} identity={item?._identity} />
                ))
              : null}
            {isShowUsers
              ? userSugestions.map(user => (
                  <UserSugestionCard
                    key={user?.id}
                    item={user}
                    onRemove={() => removeUser(user?.id)}
                  />
                ))
              : null}
            {Skeleton && !paging?.ended ? <Skeleton /> : null}
          </Slider>
        </ContainerList>
      </BlockContent>
    </Block>
  );
}
