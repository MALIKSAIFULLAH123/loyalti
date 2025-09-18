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
import { Box, CircularProgress, styled, useMediaQuery } from '@mui/material';
import React from 'react';
import {
  PAGINATION_STORY_LIST,
  WidthInteractionLandscape
} from '@metafox/story/constants';
import {
  CommentViewItem,
  ContentStoryBlock,
  ContentDetailView,
  FontComponent,
  ItemInteraction
} from '@metafox/story/components';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { useSelector } from 'react-redux';
import LoadingSkeleton from './LoadingSkeleton';
import { isEmpty } from 'lodash';
import EmptyPage from './EmptyPage';
import EndStoryView from './EndStoryView';
import PenddingView from './PenddingView';
import ButtonNextPrev from '@metafox/story/components/ButtonNextPrev';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';

const name = 'MainViewHome';

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
  const { assetUrl, jsxBackend, i18n, useGetItem } = useGlobal();
  const containerImgRef = React.useRef();
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
  const pendingViewRef = React.useRef(false);

  const story = useGetItem(identityStoryActive) as StoryItemProps;

  const {
    reactions: reactionStory,
    related_comments,
    type,
    background: backgroundIdentity,
    expand_link
  } = story || {};

  const background = useGetItem(backgroundIdentity);

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, PAGINATION_STORY_LIST)
    ) || initPagingState();

  const dataPaging = paging?.ids;

  const imgThumb = getImageSrc(
    background?.image || story?.thumbs || story?.image,
    '50x50,50',
    assetUrl('story.no_image')
  );

  const FlyReaction = jsxBackend.get('story.ui.flyReaction');
  const StatisticSendReaction = jsxBackend.get(
    'story.ui.statisticSendReaction'
  );
  const CommentStatisticStory = jsxBackend.get(
    'story.ui.commentStatisticStory'
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

  if (!paging?.initialized) {
    if (!LoadingSkeleton) {
      return <div>{i18n.formatMessage({ id: 'loading_dots' })}</div>;
    }

    return (
      <LoadingSkeleton
        isMinHeight={isMinHeight}
        isSmallHeight={isSmallHeight}
      />
    );
  }

  if (isEmpty(dataPaging)) {
    return (
      <RootEmptyStyled>
        <EmptyPage title="no_stories_found" icon="ico-story" />
      </RootEmptyStyled>
    );
  }

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
      <FontComponent />
      <ContentWrapper>
        <ButtonNextPrev position="left" show={hasPrev} onClick={handlePrev} />
        <ItemWrapper
          style={{ width: isMinHeight ? WidthInteractionLandscape : 'auto' }}
        >
          <StoryImage ref={containerImgRef} width={width}>
            <HeaderContainer>
              <HeaderStory
                identity={identityStoryActive}
                isMinHeight={isMinHeight}
                isSmallHeight={isSmallHeight}
              />
            </HeaderContainer>
            <ImageWrapper
              data-testid="imageWrapperStory"
              height={height}
              width={width}
            >
              {!readyStateFile || buffer ? (
                <Box
                  sx={{
                    position: 'absolute',
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    background: 'rgba(255,255,255,0.5)',
                    zIndex: 2,
                    ...(buffer && {
                      background: 'transparent'
                    })
                  }}
                >
                  <CircularProgress size={16} style={{ color: '#fff' }} />
                </Box>
              ) : null}
              <ImageThumbUrl key={imgThumb} src={imgThumb} type={type} />

              <ContentStoryBlock story={story} height={height} width={width} />
            </ImageWrapper>

            <StatisticItemStyled data-testid="statisticItem">
              {CommentStatisticStory && !isEmpty(related_comments) ? (
                <StatisticCommentStyled width={width / 2}>
                  <CommentStatisticStory
                    identity={identityStoryActive}
                    isMinHeight={isMinHeight}
                  />
                </StatisticCommentStyled>
              ) : null}
              {StatisticSendReaction && !isEmpty(reactionStory) ? (
                <StatisticReactionStyled width={width}>
                  <StatisticSendReaction
                    identity={identityStoryActive}
                    isMinHeight={isMinHeight}
                  />
                </StatisticReactionStyled>
              ) : null}
              {expand_link && (
                <MoreLinkWrapper>
                  <SeeMoreLink link={expand_link} isPreview={false} />
                </MoreLinkWrapper>
              )}
            </StatisticItemStyled>

            {FlyReaction && (
              <FlyReaction identity={identityStoryActive} data={reactions} />
            )}
          </StoryImage>
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
        </ItemWrapper>
        <ButtonNextPrev position="right" show={hasNext} onClick={handleNext} />
      </ContentWrapper>

      <ItemInteraction
        isMinHeight={isMinHeight}
        width={width}
        identity={identityStoryActive}
        {...storyContent}
      />
    </RootStyled>
  );
}

export default Base;
