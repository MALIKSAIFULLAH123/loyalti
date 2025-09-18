import { useGlobal } from '@metafox/framework';
import { ReactionListButton } from '@metafox/story/components';
import CommentItem from '@metafox/story/components/ItemInteraction/CommentItem';
import { WidthInteractionLandscape } from '@metafox/story/constants';
import { useStoryViewContext } from '@metafox/story/hooks';
import { LineIcon } from '@metafox/ui';
import { Box, Typography, styled, useMediaQuery } from '@mui/material';
import React from 'react';

const name = 'ItemInteractionMobile';

const FooterOnwerStyled = styled(Box, {
  slot: 'FooterOnwerStyled',
  shouldForwardProp: props =>
    props !== 'showView' &&
    props !== 'width' &&
    props !== 'isMobile' &&
    props !== 'isMinHeight' &&
    props !== 'isSmallWidth'
})<{
  showView?: boolean;
  width?: any;
  isMobile?: boolean;
  isMinHeight?: boolean;
  isSmallWidth?: boolean;
}>(({ theme, showView, width, isMobile, isMinHeight, isSmallWidth }) => ({
  minHeight: '64px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  ...(showView && {
    cursor: 'pointer'
  }),
  ...(width && {
    width
  }),
  ...(isMobile && {
    padding: theme.spacing(0, 2),
    paddingRight: theme.spacing(0.5),
    ...(isMinHeight && {
      maxWidth: width
    }),
    ...(isSmallWidth && {
      width: '100%'
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

const FooterStyled = styled(Box, {
  slot: 'FooterStyled',
  shouldForwardProp: props =>
    props !== 'showView' &&
    props !== 'width' &&
    props !== 'isMobile' &&
    props !== 'isMinHeight' &&
    props !== 'isSmallWidth'
})<{
  showView?: boolean;
  width?: any;
  isMobile?: boolean;
  isMinHeight?: boolean;
  isSmallWidth?: boolean;
}>(({ theme, showView, width, isMobile, isMinHeight, isSmallWidth }) => ({
  minHeight: '64px',
  display: 'flex',
  alignItems: 'center',
  ...(isMobile && {
    padding: theme.spacing(0, 2),
    ...(isSmallWidth && {
      width: '100%'
    }),
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

const ViewTextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(16)
}));
const ToggleIconStyled = styled(LineIcon)(({ theme }) => ({
  color: '#fff',
  marginRight: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(18),
  fontWeight: theme.typography.fontWeightBold
}));

const ViewWrapper = styled(Box, { name, slot: 'ViewWrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

function ItemInteractionMobile({
  item,
  isMinHeight = false,
  width,
  handleViewDetail,
  isOwner
}) {
  const storyContent = useStoryViewContext();

  const { useIsMobile, i18n } = useGlobal();
  const isSmallWidth = useMediaQuery('(max-width:560px)');

  const isMobile = useIsMobile();
  const isTablet = useIsMobile(true);

  if (isOwner) {
    return (
      <FooterOnwerStyled
        isMobile={isMobile}
        width={isMinHeight ? WidthInteractionLandscape : width}
        isMinHeight={isMinHeight}
        isSmallWidth={isSmallWidth}
      >
        <ViewWrapper onClick={handleViewDetail}>
          <ToggleIconStyled icon="ico-angle-up" />
          <ViewTextStyled variant="h4" color={'text.primary'}>
            {i18n.formatMessage({
              id: 'story_details'
            })}
          </ViewTextStyled>
        </ViewWrapper>
      </FooterOnwerStyled>
    );
  }

  return (
    <FooterStyled
      isMobile={isMobile}
      width={isMinHeight ? WidthInteractionLandscape : width}
      isMinHeight={isMinHeight}
      isSmallWidth={isSmallWidth}
    >
      {isTablet ? <CommentItem onClick={handleViewDetail} item={item} /> : null}
      <ReactionListButton item={item} contextStory={storyContent} />
    </FooterStyled>
  );
}

export default ItemInteractionMobile;
