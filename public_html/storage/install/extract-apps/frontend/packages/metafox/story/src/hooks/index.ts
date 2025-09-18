import React from 'react';
import { AddFormContext, StoryViewContext } from '../context';
import {
  useGetItems,
  useGlobal,
  GlobalState,
  getPagingSelector,
  PagingState
} from '@metafox/framework';
import { StoryContextProps } from '../context/StoryViewContext';
import { STORY_IMAGE_RADIO } from '../constants';
import { AddStoryContextProps } from '../context/AddFormContext';
import { useSelector } from 'react-redux';
import { AppState } from '../types';
import { getMutedStatus } from '../selectors';
import { shouldPreload } from './connectStoryArchive';
import { createSelector } from 'reselect';

interface UseStoryProps {
  hasPrev: boolean;
  hasNext: boolean;
  handlePrev: any;
  handleNext: any;
  loadmore?: (index?: number, onSuccess?: () => void) => void;
}

export default function useAddFormContext(): AddStoryContextProps {
  return React.useContext(AddFormContext);
}

export function useStoryViewContext(): StoryContextProps {
  return React.useContext(StoryViewContext);
}

export function useStory(): UseStoryProps {
  const { navigate, useGetItem } = useGlobal();
  const {
    listUserStories,
    identityUserStoryActive,
    identityStoryActive,
    fire
  } = useStoryViewContext();

  const userStoryActive = useGetItem(identityUserStoryActive);

  const story = useGetItem(identityStoryActive);

  const stories = useGetItems(userStoryActive?.stories);

  const storyIndex = stories?.findIndex(item => item?.id === story?.id);

  const userStoryIndex = listUserStories?.findIndex(
    item => item?.id === userStoryActive?.id
  );

  const firstStory = stories?.[0]?.id === story?.id;

  const lastStory = userStoryActive?.stories.length - 1 === storyIndex;

  const firstUsernFirstStory =
    listUserStories?.[0]?.id === userStoryActive?.id && firstStory;

  const lastUsernLastStory =
    listUserStories?.length - 1 === userStoryIndex && lastStory;

  const hasPrev = firstUsernFirstStory ? false : true;

  const hasNext = lastUsernLastStory ? false : true;

  const handlePrev = React.useCallback(() => {
    if (!hasPrev || storyIndex === -1) return;

    if (firstStory) {
      fire({
        type: 'setIdentityUserS_Active',
        payload: listUserStories[userStoryIndex - 1]?._identity
      });

      return;
    }

    fire({
      type: 'setIdentityStoryActive',
      payload: stories?.[storyIndex - 1]?._identity
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    hasPrev,
    storyIndex,
    firstStory,
    listUserStories,
    stories,
    userStoryIndex
  ]);

  const handleNext = React.useCallback(() => {
    if (lastUsernLastStory) {
      navigate('/');

      return;
    }

    if (!hasNext || storyIndex === -1) return;

    if (lastStory) {
      fire({
        type: 'setIdentityUserS_Active',
        payload: listUserStories[userStoryIndex + 1]?._identity
      });

      return;
    }

    fire({
      type: 'setIdentityStoryActive',
      payload: stories?.[storyIndex + 1]?._identity
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    lastUsernLastStory,
    hasNext,
    storyIndex,
    lastStory,
    listUserStories,
    userStoryIndex,
    stories
  ]);

  return { hasPrev, hasNext, handlePrev, handleNext };
}

export function useGetSizeContainer(containerRef): any {
  const [width, setWidth] = React.useState(0);
  const [height, setHeight] = React.useState(0);
  const refResize = React.useRef<ResizeObserver>();

  const onResize = React.useCallback(() => {
    if (!containerRef?.current) return [width, height];

    const containImageRect: any =
      containerRef?.current?.getBoundingClientRect();

    let widthImage = containImageRect?.height / STORY_IMAGE_RADIO;
    let heightImage = containImageRect?.height;

    if (window.screen.width <= widthImage) {
      widthImage = window.screen.width;
      heightImage = widthImage * STORY_IMAGE_RADIO;
    }

    setHeight(heightImage);
    setWidth(widthImage);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [containerRef?.current]);

  React.useEffect(() => {
    const element = containerRef.current;

    if (!element) return;

    // need listener observer because div height will change when load image
    refResize.current = new ResizeObserver(() => {
      // Do what you want to do when the size of the element changes
      onResize();
    });

    onResize();
    refResize.current?.observe(element);

    // clean up
    return () => {
      refResize?.current.disconnect();
    }; // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onResize, containerRef?.current]);

  return [width, height, onResize];
}

export function useGetMutedStatus(): AppState['storyStatus'] {
  return useSelector<GlobalState, AppState['storyStatus']>(state =>
    getMutedStatus(state)
  );
}

export function useArchiveStory(): UseStoryProps {
  const context = useStoryViewContext();
  const {
    fire,
    stories: storyIdentities,
    indexStoryActive: storyIndex,
    nextDate,
    prevDate,
    identityStoryActive,
    loading,
    total,
    positionStory,
    pagingId
  } = context;

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || {};

  const { prev_page, last_page, current_page } = paging?.pagesOffset || {};

  const { useGetItem, useGetItems, dispatch, usePageParams } = useGlobal();

  const pageParams = usePageParams();

  const story = useGetItem(identityStoryActive);
  const stories = useGetItems(storyIdentities);

  const firstStory = stories?.[0]?.id === story?.id && prev_page === 0;

  const lastStory =
    storyIdentities?.[storyIdentities?.length - 1] === identityStoryActive &&
    last_page === current_page;

  const firstDatenFirstStory = !prevDate && firstStory;

  const lastDatenLastStory = !nextDate && lastStory;

  const hasPrev = firstDatenFirstStory ? false : true;

  const hasNext = lastDatenLastStory ? false : true;

  const loadingNext =
    storyIdentities?.[storyIdentities?.length - 1] === identityStoryActive &&
    last_page !== current_page;
  const loadingPrev = stories?.[0]?.id === story?.id && prev_page !== 0;

  const loadmore = (position, onSuccess = () => {}) => {
    const handleSuccessLoadMore = pagingData => {
      const pagingId = pagingData?.pagingId;
      const stories = pagingData?.ids;

      if (!pagingId || !stories?.length) return;

      const total = pagingData?.pagesOffset?.total;

      fire({
        type: 'setInit',
        payload: {
          stories,
          total,
          loading: false
        }
      });
    };

    const direction = shouldPreload(context?.stories?.length, position);

    if (!direction) {
      onSuccess && onSuccess();

      return;
    }

    onSuccess && onSuccess();

    dispatch({
      type: 'story/story_archive/LOAD',
      payload: {
        user_id: pageParams?.user_id,
        date: context?.date,
        direction
      },
      meta: {
        onSuccess: handleSuccessLoadMore
      }
    });
  };

  const handleSuccess = (pagingData, date) => {
    const pagingId = pagingData?.pagingId;
    const stories = pagingData?.ids;

    if (!pagingId || !stories?.length) return;

    const total = pagingData?.pagesOffset?.total;
    const nextDate = pagingData?.pagesOffset?.next_date;
    const prevDate = pagingData?.pagesOffset?.prev_date;

    fire({
      type: 'setLoading',
      payload: false
    });

    fire({
      type: 'setInit',
      payload: {
        isReady: false,
        stories,
        total,
        nextDate,
        prevDate,
        indexStoryActive: 0,
        positionStory: 0,
        identityStoryActive: stories?.[0],
        pagingId,
        date
      }
    });
  };

  const handleFail = () => {
    fire({
      type: 'setLoading',
      payload: false
    });
  };

  const handlePrev = React.useCallback(() => {
    if (loadingPrev) {
      fire({
        type: 'setLoading',
        payload: true
      });
    }

    if (loading || !hasPrev || loadingPrev) return;

    if (firstStory) {
      fire({
        type: 'setLoading',
        payload: true
      });

      dispatch({
        type: 'story/story_archive/LOAD',
        payload: {
          user_id: pageParams?.user_id,
          date: prevDate
        },
        meta: {
          onSuccess: data => handleSuccess(data, prevDate),
          onError: handleFail
        }
      });

      return;
    }

    loadmore(storyIndex - 1);

    fire({
      type: 'setStoryActive',
      payload: {
        identity: storyIdentities?.[storyIndex - 1],
        index: storyIndex - 1,
        positionStory: positionStory - 1
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    hasPrev,
    storyIdentities,
    storyIndex,
    firstStory,
    loading,
    loadingPrev,
    loadmore,
    positionStory,
    stories,
    prevDate
  ]);

  const handleNext = React.useCallback(() => {
    if (loadingNext) {
      fire({
        type: 'setLoading',
        payload: true
      });
    }

    if (loading || !hasNext || loadingNext) return;

    if (lastStory) {
      fire({
        type: 'setLoading',
        payload: true
      });
      dispatch({
        type: 'story/story_archive/LOAD',
        payload: {
          user_id: pageParams?.user_id,
          date: nextDate
        },
        meta: {
          onSuccess: data => handleSuccess(data, nextDate),
          onError: handleFail
        }
      });

      return;
    }

    loadmore(storyIndex + 1);

    fire({
      type: 'setStoryActive',
      payload: {
        identity: storyIdentities?.[storyIndex + 1],
        index: storyIndex + 1,
        positionStory: positionStory + 1
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    hasNext,
    storyIdentities,
    storyIndex,
    lastStory,
    total,
    loadingNext,
    loading,
    loadmore,
    nextDate,
    positionStory,
    stories
  ]);

  return { hasPrev, hasNext, handlePrev, handleNext, loadmore };
}

// TODO: Hotfix issue import - Remove when update next version
const getReactions = (state: GlobalState) =>
  (state.preaction.data.reactions || []).filter(item => item.is_active);

const getReactionSelector = createSelector(getReactions, data => data);

export function useReactionTemporary() {
  return useSelector(getReactionSelector);
}
