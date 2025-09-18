import {
  BlockViewProps,
  GlobalState,
  PagingState,
  getPagingSelector,
  useGetItems,
  useGlobal
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import {
  PAGINATION_STORY_LIST,
  TYPE_LIVE_VIDEO
} from '@metafox/story/constants';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { reducerStoryView } from '@metafox/story/context/reducerStoryView';
import { useGetMutedStatus } from '@metafox/story/hooks';
import {
  StoryUserProps,
  PauseStatus,
  StoryItemProps
} from '@metafox/story/types';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useReducer } from 'react';
import { useSelector } from 'react-redux';

export interface Props extends BlockViewProps {}

const name = 'StoryViewBlock';
const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  display: 'flex',
  width: '100%',
  height: '100%'
}));

const SideBarWrapper = styled(Box, {
  name,
  slot: 'SideBarWrapper',
  overridesResolver: (props, styles) => [styles.buddyWrap]
})(({ theme }) => ({
  width: '360px',
  backgroundColor: theme.palette.background.paper
}));

const ContainerView = styled(Box, {
  name,
  slot: 'room-wrap'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center'
}));

export default function Base(props: Props) {
  const { jsxBackend, dispatch, createErrorPage, useGetItem } = useGlobal();
  const refBlur = React.useRef(false);
  const refBlurTimeout = React.useRef();

  const [state, fire] = useReducer(reducerStoryView, {
    ...initStateStory
  });

  const {
    isReady,
    listUserStories,
    identityStoryActive,
    identityUserStoryActive,
    pauseStatus
  } = state || {};

  const { muted: mutedStore, isEditMuted = false } = useGetMutedStatus();

  const userStoryActive = useGetItem(identityUserStoryActive) as StoryUserProps;
  const storyActive = useGetItem(identityStoryActive) as StoryItemProps;

  const stories = useGetItems(userStoryActive?.stories);
  const initRef = React.useRef<any>(false);

  const listUserPaging = useSelector<GlobalState, PagingState>(
    (state: GlobalState) => getPagingSelector(state, PAGINATION_STORY_LIST)
  );

  const indexStoryActive = stories?.findIndex(
    item => item?.id === storyActive?.id
  );

  const lastStory = React.useMemo(() => {
    return stories.length - 1 === indexStoryActive;
  }, [stories, indexStoryActive]);

  const SideBar = jsxBackend.get('story.block.sidebarHomeStory');
  const MainView = jsxBackend.get('story.block.mainViewHome');
  const SideAppHeader = jsxBackend.get('story.block.sideStoryHeader');

  React.useEffect(() => {
    fire({
      type: 'setReady',
      payload: false
    });
  }, []);

  React.useEffect(() => {
    if (initRef.current) return;

    if (!stories?.length) return;

    initRef.current = true;
    const indexFirstNew = stories?.findIndex(item => {
      if (item.type === TYPE_LIVE_VIDEO && item?.extra_params?.is_streaming)
        return true;

      if (item.has_seen === false) return true;

      return false;
    });

    fire({
      type: 'setIdentityStoryActive',
      payload:
        indexFirstNew === -1
          ? stories[0]?._identity
          : stories[indexFirstNew]?._identity
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [stories?.length]);

  React.useEffect(() => {
    const indexFirstNew = stories?.findIndex(item => {
      if (item.type === TYPE_LIVE_VIDEO && item?.extra_params?.is_streaming)
        return true;

      if (item.has_seen === false) return true;

      return false;
    });

    fire({
      type: 'setIdentityStoryActive',
      payload:
        indexFirstNew === -1
          ? stories[0]?._identity
          : stories[indexFirstNew]?._identity
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userStoryActive?.id, listUserStories?.length]);

  React.useEffect(() => {
    if (isEmpty(storyActive)) return;

    if (!storyActive?.in_process && !storyActive.has_seen) {
      if (!isReady && !window.navigator?.userActivation?.hasBeenActive) return;

      dispatch({
        type: 'story/updateView',
        payload: {
          story: storyActive,
          lastStory,
          identityUser: identityUserStoryActive
        }
      });
    }

    fire({ type: 'setReactions', payload: [] });
    fire({ type: 'setOpenViewComment', payload: false });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [storyActive?._identity, lastStory, isReady]);

  React.useEffect(() => {
    if (isEmpty(storyActive)) return;

    if (isEditMuted && window.navigator?.userActivation?.hasBeenActive) {
      fire({
        type: 'setMuted',
        payload: mutedStore
      });
    }
  }, [isEditMuted, mutedStore, fire, storyActive]);

  if (listUserPaging?.error) {
    return createErrorPage(listUserPaging?.error, { loginRequired: true });
  }

  const handleHover = () => {
    if (pauseStatus === PauseStatus.Force) return;

    fire({ type: 'setForcePause', payload: PauseStatus.Pause });
  };

  const handleLeave = () => {
    if (pauseStatus === PauseStatus.Force) return;

    fire({ type: 'setForcePause', payload: PauseStatus.No });
  };

  const onBlurContainerView = e => {
    if (pauseStatus === PauseStatus.Force) return;

    if (
      e.relatedTarget !== e.currentTarget &&
      !e.currentTarget.contains(e.relatedTarget)
    ) {
      refBlur.current = true;
      fire({ type: 'setForcePause', payload: PauseStatus.Pause });
      fire({ type: 'setOpenActionItem', payload: true });

      return;
    }

    fire({ type: 'setForcePause', payload: PauseStatus.No });
    fire({ type: 'setOpenActionItem', payload: false });
  };

  const onMouseEnterContainerView = e => {
    if (pauseStatus === PauseStatus.Force) return;

    if (!refBlur.current) return;

    refBlurTimeout.current = setTimeout(() => {
      fire({ type: 'setForcePause', payload: PauseStatus.No });
      fire({ type: 'setOpenActionItem', payload: false });
      refBlur.current = false;
    }, 300);
  };

  const onMouseLeaveContainerView = e => {
    clearTimeout(refBlurTimeout.current);
  };

  return (
    <StoryViewContext.Provider value={{ ...state, fire, indexStoryActive }}>
      <Root>
        <SideBarWrapper onMouseEnter={handleHover} onMouseLeave={handleLeave}>
          <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
            <SideAppHeader />
            <SideBar />
          </ScrollContainer>
        </SideBarWrapper>
        <ContainerView
          onBlur={onBlurContainerView}
          onMouseEnter={onMouseEnterContainerView}
          onMouseLeave={onMouseLeaveContainerView}
          tabIndex={0}
        >
          <MainView />
        </ContainerView>
      </Root>
    </StoryViewContext.Provider>
  );
}
