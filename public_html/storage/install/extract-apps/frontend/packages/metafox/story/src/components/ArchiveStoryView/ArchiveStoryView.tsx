import { useGlobal } from '@metafox/framework';
import {
  useArchiveStory,
  useGetSizeContainer,
  useStoryViewContext
} from '@metafox/story/hooks';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, useMediaQuery } from '@mui/material';
import React from 'react';
import ArchiveHeaderStory from './ArchiveHeaderStory';
import { isEmpty } from 'lodash';
import ReactionListButton from '../ReactionListButton/ReactionListButton';
import ContentStoryBlock from '../ContentBlock';
import FontComponent from '../FontComponent';
import LoadingCircular from '../LoadingCircular';
import ButtonNextPrev from '../ButtonNextPrev';

const name = 'ArchiveStoryView';

const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled',
  shouldForwardProp: props => props !== 'isOwner' && props !== 'isMobile'
})<{ isOwner?: boolean; isMobile?: boolean }>(
  ({ theme, isOwner, isMobile }) => ({
    width: '100%',
    height: '100%',
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    userSelect: 'none',
    backgroundColor: '#000',
    flexDirection: 'column'
  })
);

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

const StatisticItemStyled = styled(Box, {
  name,
  slot: 'StatisticItem',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, height, width }) => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  padding: theme.spacing(1.5),
  ...(width && { width }),
  overflow: 'hidden'
}));

const FooterStyled = styled(Box, {
  slot: 'FooterStyled',
  shouldForwardProp: props =>
    props !== 'showView' &&
    props !== 'width' &&
    props !== 'isMobile' &&
    props !== 'isMinHeight'
})<{
  showView?: boolean;
  width?: any;
  isMobile?: boolean;
  isMinHeight?: boolean;
}>(({ theme, width, isMobile, isMinHeight }) => ({
  minHeight: '64px',
  display: 'flex',
  alignItems: 'center',
  ...(width && { width }),
  ...(isMobile && {
    padding: theme.spacing(0, 2),
    width: '100%',
    ...(isMinHeight && {
      maxWidth: '560px'
    }),
    overflowX: 'auto',
    MsOverflowStyle: 'none',
    scrollbarWidth: 'none',
    scrollBehavior: 'smooth',
    '&::-webkit-scrollbar': {
      display: 'none'
    }
  })
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

interface Props {
  story: any;
  isModal?: boolean;
  isOwner?: boolean;
}

function ArchiveStoryView(props: Props) {
  const { assetUrl, useIsMobile, jsxBackend, getSetting, useGetItem } =
    useGlobal();

  const { story, isOwner = false } = props || {};

  const { handleNext, handlePrev, hasNext, hasPrev } = useArchiveStory();

  const storyContent = useStoryViewContext();
  const { reactions, readyStateFile, buffer, loading } = storyContent || {};

  const isMobile = useIsMobile(true);
  const disableReact = !getSetting('preaction');
  const isMinHeight = useMediaQuery('(max-height:667px)');
  const isSmallHeight = useMediaQuery('(max-height:580px)');

  const containerImgRef = React.useRef();

  const FlyReaction = jsxBackend.get('story.ui.flyReaction');
  const StatisticSendReaction = jsxBackend.get(
    'story.ui.statisticSendReaction'
  );

  const [width, height] = useGetSizeContainer(containerImgRef);

  const {
    _identity,
    reactions: reactionStory,
    type,
    background: backgroundIdentity
  } = story || {};

  const background = useGetItem(backgroundIdentity);

  const imgThumb = getImageSrc(
    background?.image || story?.thumbs || story?.image,
    '50x50,50',
    assetUrl('story.no_image')
  );

  return (
    <RootStyled isOwner={isOwner} isMobile={isMobile}>
      <FontComponent />
      <ContentWrapper>
        <ButtonNextPrev
          position="left"
          show={hasPrev}
          onClick={handlePrev}
          sxProps={
            isMobile && isSmallHeight
              ? {
                  '& > div': {
                    ...(!hasPrev && { display: 'flex', visibility: 'hidden' })
                  }
                }
              : null
          }
        />
        <ItemWrapper>
          <StoryImage ref={containerImgRef} width={width}>
            <HeaderContainer>
              <ArchiveHeaderStory
                {...props}
                story={story}
                isMinHeight={isMinHeight}
                isSmallHeight={isSmallHeight}
              />
            </HeaderContainer>
            <ImageWrapper height={height} width={width}>
              {loading || !readyStateFile || buffer ? (
                <LoadingCircular
                  sx={{
                    ...(buffer && {
                      background: 'transparent'
                    })
                  }}
                />
              ) : null}
              <ImageThumbUrl key={imgThumb} src={imgThumb} type={type} />

              <ContentStoryBlock story={story} height={height} width={width} />
            </ImageWrapper>
            <StatisticItemStyled width={width}>
              {StatisticSendReaction && !isEmpty(reactionStory) ? (
                <StatisticSendReaction
                  identity={_identity}
                  isMinHeight={isMinHeight}
                />
              ) : null}
            </StatisticItemStyled>
            {FlyReaction && (
              <FlyReaction identity={_identity} data={reactions} />
            )}
          </StoryImage>
        </ItemWrapper>
        <ButtonNextPrev
          position="right"
          show={hasNext}
          onClick={handleNext}
          sxProps={
            isMobile && isSmallHeight
              ? {
                  '& > div': {
                    ...(!hasNext && { display: 'flex', visibility: 'hidden' })
                  }
                }
              : null
          }
        />
      </ContentWrapper>
      {isMobile || disableReact || isOwner ? null : (
        <FooterStyled
          width={width}
          isMobile={isMobile}
          isMinHeight={isMinHeight}
        >
          <ReactionListButton item={story} contextStory={storyContent} />
        </FooterStyled>
      )}
    </RootStyled>
  );
}

export default ArchiveStoryView;
