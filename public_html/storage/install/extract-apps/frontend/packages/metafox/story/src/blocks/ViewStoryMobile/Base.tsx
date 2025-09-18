import {
  BlockViewProps,
  GlobalState,
  PagingState,
  getPagingSelector,
  useGetItems,
  useGlobal
} from '@metafox/framework';
import {
  PAGINATION_STORY_LIST,
  TYPE_LIVE_VIDEO
} from '@metafox/story/constants';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { reducerStoryView } from '@metafox/story/context/reducerStoryView';
import { useGetMutedStatus } from '@metafox/story/hooks';
import { StoryUserProps, StoryItemProps } from '@metafox/story/types';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useReducer } from 'react';
import { useSelector } from 'react-redux';

export interface Props extends BlockViewProps {}

const name = 'StoryViewMobile';

const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  display: 'flex',
  width: '100%',
  height: '100%',
  position: 'fixed',
  top: 0,
  right: 0,
  left: 0
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

  const [state, fire] = useReducer(reducerStoryView, {
    ...initStateStory
  });

  const initRef = React.useRef<any>(false);

  const { muted: mutedStore, isEditMuted = false } = useGetMutedStatus();

  const {
    identityStoryActive,
    identityUserStoryActive,
    isReady,
    listUserStories
  } = state || {};

  const userStoryActive = useGetItem(identityUserStoryActive) as StoryUserProps;
  const stories = useGetItems(userStoryActive?.stories);

  const listUserPaging = useSelector<GlobalState, PagingState>(
    (state: GlobalState) => getPagingSelector(state, PAGINATION_STORY_LIST)
  );

  const storyActive = useGetItem(identityStoryActive) as StoryItemProps;

  const indexStoryActive = stories?.findIndex(
    item => item?.id === storyActive?.id
  );

  const lastStory = React.useMemo(() => {
    return stories.length - 1 === indexStoryActive;
  }, [stories, indexStoryActive]);

  const SideBar = jsxBackend.get('story.block.sidebarHomeStory');
  const MainView = jsxBackend.get('story.block.mainViewHomeMobile');

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

  return (
    <StoryViewContext.Provider value={{ ...state, fire, indexStoryActive }}>
      <Root>
        <SideBarWrapper style={{ display: 'none' }}>
          <SideBar />
        </SideBarWrapper>
        <ContainerView>
          <MainView />
        </ContainerView>
      </Root>
    </StoryViewContext.Provider>
  );
}
