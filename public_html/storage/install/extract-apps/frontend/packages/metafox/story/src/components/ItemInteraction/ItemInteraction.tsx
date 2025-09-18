import { useGlobal } from '@metafox/framework';
import { Box, Tooltip, Typography, styled, useMediaQuery } from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import ReactionListButton from '../ReactionListButton/ReactionListButton';
import CommentItem from './CommentItem';
import { camelCase, isEmpty } from 'lodash';
import { StoryItemProps } from '@metafox/story/types';
import { StoryContextProps } from '@metafox/story/context/StoryViewContext';
import { WidthInteractionLandscape } from '@metafox/story/constants';

const name = 'ItemInteraction';

const RootOwnerStyled = styled(Box, {
  slot: 'RootOwnerStyled',
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
      maxWidth: '400px'
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

const RootStyled = styled(Box, {
  slot: 'RootStyled',
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

const CommentIconStyled = styled(LineIcon)(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(20),
  fontWeight: theme.typography.fontWeightBold
}));

const ViewWrapper = styled(Box, { name, slot: 'ViewWrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));
const CommentWrapper = styled(Box, { name, slot: 'CommentWrapper' })(
  ({ theme }) => ({
    padding: theme.spacing(1.5)
  })
);

interface Props {
  identity: string;
  width?: any;
  [key: string]: any;
}

function ItemInteraction({
  width,
  identity,
  isMinHeight = false,
  ...rest
}: Props) {
  const { getSetting, useSession, i18n, isMobile, useGetItem } = useGlobal();
  const isSmallWidth = useMediaQuery('(max-width:560px)');
  const enableCommentApp = getSetting('comment');

  const { identityUserStoryActive, openStoryDetail, openViewComment, fire } =
    rest || {};

  const userStoryActive = useGetItem(identityUserStoryActive);
  const storyActive = useGetItem(identity);

  const { user } = useSession();

  if (isEmpty(storyActive)) return <RootStyled width={width} />;

  const { statistic, extra } = storyActive || {};

  const disableReact = !getSetting('preaction') || !extra?.can_like;

  const handleViewDetail = () => {
    if (openViewComment) {
      fire({ type: 'setOpenViewComment', payload: false });
    }

    fire({ type: 'setOpenStoryDetail', payload: !openStoryDetail });
  };

  const handleViewComment = () => {
    if (openStoryDetail) {
      fire({ type: 'setOpenStoryDetail', payload: false });
    }

    fire({ type: 'setOpenViewComment', payload: !openViewComment });
  };

  const isOwner = userStoryActive?.id === user?.id;

  if (isOwner) {
    return (
      <RootOwnerStyled
        data-testid="itemInteraction"
        isMobile={isMobile}
        width={isMinHeight ? WidthInteractionLandscape : width}
        showView={isOwner}
        isMinHeight={isMinHeight}
        isSmallWidth={isSmallWidth}
      >
        <ViewWrapper
          data-testid="itemInteractionView"
          onClick={handleViewDetail}
        >
          <ToggleIconStyled icon="ico-angle-up" />
          <ViewTextStyled variant="h4" color={'text.primary'}>
            {i18n.formatMessage(
              { id: 'total_story_viewer' },
              { value: statistic?.total_view || 0 }
            )}
          </ViewTextStyled>
        </ViewWrapper>
        {enableCommentApp ? (
          <CommentWrapper
            data-testid="itemInteractionComments"
            onClick={handleViewComment}
          >
            <Tooltip title={i18n.formatMessage({ id: 'comments' })}>
              <CommentIconStyled icon="ico-comment-o" />
            </Tooltip>
          </CommentWrapper>
        ) : null}
      </RootOwnerStyled>
    );
  }

  return (
    <RootStyled
      data-testid="itemInteraction"
      width={isMinHeight ? WidthInteractionLandscape : width}
      isMobile={isMobile}
      isMinHeight={isMinHeight}
      isSmallWidth={isSmallWidth}
    >
      {enableCommentApp ? (
        <Box data-testid={camelCase('field comment')}>
          <CommentItem onClick={handleViewComment} item={storyActive} />
        </Box>
      ) : null}
      {/* <ReplyItem />  */}
      {disableReact ? null : (
        <ReactionListButton
          item={storyActive as StoryItemProps}
          contextStory={{ ...rest } as StoryContextProps}
        />
      )}
    </RootStyled>
  );
}

export default ItemInteraction;
