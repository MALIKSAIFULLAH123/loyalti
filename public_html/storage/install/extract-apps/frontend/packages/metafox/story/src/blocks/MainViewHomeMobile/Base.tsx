import {
  GlobalState,
  PagingState,
  getPagingSelector,
  initPagingState,
  useGlobal
} from '@metafox/framework';
import HeaderStory from '@metafox/story/components/HeaderStory/HeaderStory';
import {
  useGetSizeContainer,
  useStory,
  useStoryViewContext
} from '@metafox/story/hooks';
import { getImageSrc } from '@metafox/utils';
import { Box, useMediaQuery } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import {
  PAGINATION_STORY_LIST,
  WidthInteractionLandscape
} from '@metafox/story/constants';
import {
  CommentViewItem,
  ContentStoryBlock,
  ContentDetailView,
  ItemInteraction,
  LoadingCircular,
  ButtonNextPrev
} from '@metafox/story/components';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { useSelector } from 'react-redux';
import LoadingSkeleton from './LoadingSkeleton';
import { isEmpty } from 'lodash';
import EmptyPage from './EmptyPage';
import EndStoryView from '../MainViewHome/EndStoryView';
import PenddingView from '../MainViewHome/PenddingView';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';

const name = 'MainViewHomeMobile';

const RootStyled = styled(Box, { name, slot: 'RootStyled' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  userSelect: 'none',
  backgroundColor: '#000',
  paddingTop: theme.spacing(1.5),
  flexDirection: 'column'
}));

const ContentWrapper = styled(Box, { name, slot: 'ContentWrapper' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flex: 1,
    minHeight: 0
  })
);

const RootEmptyStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  userSelect: 'none',
  backgroundColor: '#000'
}));

const HeaderContainer = styled(Box, { name, slot: 'HeaderContainer' })(
  ({ theme }) => ({
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 2,
    '&::before': {
      content: "''",
      position: 'absolute',
      top: 0,
      left: 0,
      right: 0,
      width: '100%',
      height: '100%',
      backgroundImage:
        'linear-gradient(180deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.08), transparent)',
      zIndex: -1,
      pointerEvents: 'none'
    }
  })
);

const ItemWrapper = styled(Box, {
  name,
  slot: 'ItemWrapper'
})(({ theme }) => ({
  position: 'relative',
  margin: 'auto',
  backgroundColor: '#000',
  display: 'flex',
  justifyContent: 'center',
  borderRadius: theme.shape.borderRadius,
  height: '100%'
}));
const StoryImage = styled('div', {
  name,
  slot: 'StoryImage',
  shouldForwardProp: props => props !== 'width'
})<{ height?: number; width?: number }>(({ theme, width }) => ({
  height: '100%',
  width,
  position: 'relative'
}));

const ImageWrapper = styled('div', {
  name: 'Image',
  slot: 'ImageWrapper',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, height, width }) => ({
  position: 'relative',
  display: 'flex',
  alignItems: 'center',

  height,
  width,
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden'
}));

const ImageThumbUrl = styled('img', {
  name,
  slot: 'ImageThumbUrl',
  shouldForwardProp: props =>
    props !== 'width' && props !== 'height' && props !== 'type'
})<{ height?: number; width?: number; type?: string }>(
  ({ theme, height, width, type }) => ({
    borderRadius: theme.shape.borderRadius,
    objectFit: 'contain',
    position: 'absolute',
    inset: 0,
    width: '100%',
    height: '100%',
    filter: 'blur(30px)',
    WebkitFilter: 'blur(30px)',
    transform: 'scale(2)'
  })
);

const StatisticItemStyled = styled(Box, {
  name,
  slot: 'StatisticItem'
})(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  zIndex: 2,
  padding: theme.spacing(1.5, 0),
  width: '100%',
  overflow: 'hidden'
}));

const StatisticCommentStyled = styled(Box, {
  name,
  slot: 'StatisticComment',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, width }) => ({
  padding: theme.spacing(0, 1.5),
  overflow: 'hidden',
  ...(width && { width })
}));

const StatisticReactionStyled = styled(Box, {
  name,
  slot: 'StatisticComment',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, width }) => ({
  padding: theme.spacing(0, 1.5),
  overflow: 'hidden',
  ...(width && { width })
}));

const MoreLinkWrapper = styled(Box, {
  name,
  slot: 'MoreLinkWrapper'
})(({ theme }) => ({
  display: 'block',
  textAlign: 'center',
  paddingTop: theme.spacing(2),
  width: '100%'
}));

function Base() {
  const { assetUrl, jsxBackend, useGetItem } = useGlobal();
  const containerImgRef = React.useRef();
  const pendingViewRef = React.useRef(false);

  const isMinHeight = useMediaQuery('(max-height:667px)');
  const isSmallHeight = useMediaQuery('(max-height:580px)');

  const [width, height] = useGetSizeContainer(containerImgRef);

  const storyContent = useStoryViewContext();

  const {
    identityStoryActive,
    identityUserStoryActive,
    reactions,
    openStoryDetail,
    openViewComment,
    readyStateFile,
    fire,
    isLastStory,
    isReady,
    buffer
  } = storyContent || {};
  const { hasPrev, hasNext, handlePrev, handleNext } = useStory();

  const story = useGetItem(identityStoryActive) as StoryItemProps;

  const {
    reactions: reactionStory,
    related_comments,
    background: backgroundIdentity,
    expand_link
  } = story || {};

  const background = useGetItem(backgroundIdentity);

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, PAGINATION_STORY_LIST)
    ) || initPagingState();

  const { initialized } = paging ?? {};
  const dataPaging = paging?.ids;

  const FlyReaction = jsxBackend.get('story.ui.flyReaction');
  const StatisticSendReaction = jsxBackend.get(
    'story.ui.statisticSendReaction'
  );
  const CommentStatisticStory = jsxBackend.get(
    'story.ui.commentStatisticStory'
  );

  const showStatisticComment = React.useMemo(
    () => CommentStatisticStory && !isEmpty(related_comments) && !isSmallHeight,
    [CommentStatisticStory, related_comments, isSmallHeight]
  );

  const imgThumb = getImageSrc(
    background?.image || story?.thumbs || story?.image,
    '50x50,50',
    assetUrl('story.no_image')
  );

  React.useEffect(() => {
    if (openStoryDetail || openViewComment) {
      fire({
        type: 'setForcePause',
        payload: PauseStatus.Force
      });

      return;
    }

    fire({
      type: 'setForcePause',
      payload: PauseStatus.No
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [openStoryDetail, openViewComment, identityStoryActive]);

  React.useEffect(() => {
    fire({
      type: 'setOpenStoryDetail',
      payload: false
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [identityUserStoryActive]);

  React.useEffect(() => {
    if (pendingViewRef.current) return;

    if (story?.in_process && !isReady && window.navigator?.userActivation) {
      if (!window.navigator.userActivation.hasBeenActive) {
        pendingViewRef.current = true;
      }
    }
  }, [story?.in_process, isReady]);

  if (!initialized) {
    return (
      <LoadingSkeleton
        isMinHeight={isMinHeight}
        isSmallHeight={isSmallHeight}
      />
    );
  }

  if (isEmpty(dataPaging))
    return (
      <RootEmptyStyled>
        <EmptyPage title="no_stories_found" icon="ico-story" />
      </RootEmptyStyled>
    );

  if (isLastStory) {
    return <EndStoryView />;
  }

  if (!story?.in_process && !isReady && window.navigator?.userActivation) {
    if (
      !window.navigator?.userActivation?.hasBeenActive &&
      !pendingViewRef.current
    )
      return <PenddingView />;
  }

  return (
    <RootStyled>
      <ContentWrapper>
        <ButtonNextPrev position="left" show={hasPrev} onClick={handlePrev} />
        <ItemWrapper
          style={{
            width: isMinHeight ? WidthInteractionLandscape : 'auto',
            height: height ? height : '100%'
          }}
        >
          <StoryImage ref={containerImgRef} width={width}>
            <HeaderContainer>
              <HeaderStory
                identity={identityStoryActive}
                isMinHeight={isMinHeight}
                isSmallHeight={isSmallHeight}
              />
            </HeaderContainer>
            <ImageWrapper height={height} width={width}>
              {!readyStateFile || buffer ? <LoadingCircular /> : null}
              <ImageThumbUrl key={imgThumb} src={imgThumb} />
              <ContentStoryBlock story={story} height={height} width={width} />
            </ImageWrapper>

            <StatisticItemStyled>
              {showStatisticComment ? (
                <StatisticCommentStyled width={width / 2}>
                  <CommentStatisticStory identity={identityStoryActive} />
                </StatisticCommentStyled>
              ) : null}
              {StatisticSendReaction && !isEmpty(reactionStory) ? (
                <StatisticReactionStyled width={width}>
                  <StatisticSendReaction identity={identityStoryActive} />
                </StatisticReactionStyled>
              ) : null}
              {expand_link && (
                <MoreLinkWrapper>
                  <SeeMoreLink
                    link={expand_link}
                    fire={fire}
                    isPreview={false}
                  />
                </MoreLinkWrapper>
              )}
            </StatisticItemStyled>
            {FlyReaction && (
              <FlyReaction identity={identityStoryActive} data={reactions} />
            )}
          </StoryImage>
        </ItemWrapper>
        <ButtonNextPrev position="right" show={hasNext} onClick={handleNext} />
      </ContentWrapper>
      <ContentDetailView
        item={story}
        isMinHeight={isMinHeight}
        open={openStoryDetail}
        setOpen={(value: any) =>
          fire({
            type: 'setOpenStoryDetail',
            payload: value
          })
        }
        hideSlider={isSmallHeight}
      />
      <CommentViewItem
        isMinHeight={isMinHeight}
        setOpen={(value: any) =>
          fire({
            type: 'setOpenViewComment',
            payload: value
          })
        }
        open={openViewComment}
        identity={identityStoryActive}
      />
      {openStoryDetail || openViewComment ? (
        <Box minHeight={64} />
      ) : (
        <ItemInteraction
          isMinHeight={isMinHeight}
          width={isMinHeight ? WidthInteractionLandscape : width}
          identity={identityStoryActive}
          {...storyContent}
        />
      )}
    </RootStyled>
  );
}

export default Base;
