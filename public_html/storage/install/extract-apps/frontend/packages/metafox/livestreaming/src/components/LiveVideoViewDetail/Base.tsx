import { connectItemView, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { DialogContent } from '@metafox/dialog';
import * as React from 'react';
import { LivestreamDetailViewProps } from '../../types';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import actionCreators from '@metafox/livestreaming/actions/livestreamItemActions';

const name = 'videoView';

const VideoContainer = styled('div', {
  name: 'VideoView',
  slot: 'dialogVideo'
})(({ theme }) => ({
  position: 'relative',
  backgroundColor: '#000',
  width: '100%',
  overflow: 'hidden',
  flex: 1,
  minWidth: 0,
  '& iframe': {
    width: '100%',
    height: '100%'
  },
  [theme.breakpoints.down('md')]: {
    width: '100%',
    height: 'auto',
    borderRadius: 0,
    overflow: 'initial'
  }
}));

const StatisticStyled = styled('div', {
  name: 'VideoView',
  slot: 'dialogStatistic',
  shouldForwardProp: prop => prop !== 'isExpand'
})<{
  isExpand: boolean;
}>(({ theme, isExpand }) => ({
  height: '100%',
  width: isExpand ? 0 : '480px',
  [theme.breakpoints.down('md')]: {
    width: '100%'
  },
  [theme.breakpoints.down('xs')]: {
    width: '100%',
    height: '400px'
  }
}));

const Root = styled(DialogContent, {
  name: 'VideoView',
  slot: 'dialogStatistic'
})<{}>(({ theme }) => ({
  padding: '0 !important',
  height: '100%',
  display: 'flex',
  overflowX: 'hidden',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column'
  }
}));

const StyledWrapperStatistic = styled(Box, { name, slot: 'WrapperStatistic' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    height: '100%'
  })
);

function LiveVideoViewDialog({
  item,
  identity,
  error,
  actions,
  user,
  searchParams
}: LivestreamDetailViewProps) {
  const { ItemDetailInteractionInModal, jsxBackend, useSession, getSetting } =
    useGlobal();
  const enableCommentApp = getSetting('comment');
  const [isExpand, setExpand] = React.useState<boolean>(false);
  const refViewed = React.useRef(false);
  const { user: authUser, loggedIn } = useSession();
  const isOwner = authUser?.id === user?.id;
  const [parentReply, setParentReply] = React.useState<
    Record<string, any> | undefined
  >();
  const [focusState, setFocusState] = React.useState<number>(0);

  const VideoItemModalView = jsxBackend.get('livestreaming.itemView.modalCard');
  const ListingComment = jsxBackend.get(
    'livestreaming.block.commentLiveListing'
  );

  React.useEffect(() => {
    if (refViewed.current || !item?.is_streaming || isOwner || !loggedIn)
      return;

    // update viewer
    refViewed.current = true;
    actions.updateViewer();

    return () => {
      actions.removeViewer();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item?.is_streaming]);

  if (!item) return null;

  const onMinimizePhoto = (minimize: boolean) => {
    setExpand(minimize);
  };

  const handleSuccess = () => {
    setParentReply(undefined);
  };

  const removeReply = () => {
    setParentReply(undefined);
  };

  const handleReply = data => {
    setParentReply(data);
    setFocusState(prev => prev + 1);
  };

  const { is_streaming } = item;
  const startFooterItems = is_streaming
    ? [
        {
          component: 'livestreaming.ui.watchingUsers',
          props: {
            streamKey: item?.stream_key
          }
        },
        {
          component: 'livestreaming.ui.composerReplyInfo',
          props: {
            item: parentReply,
            removeReply,
            sx: { mt: 1 }
          }
        }
      ]
    : undefined;

  return (
    <ErrorBoundary error={error}>
      <Root>
        <VideoContainer>
          {VideoItemModalView ? (
            <VideoItemModalView
              item={item}
              onMinimizePhoto={onMinimizePhoto}
              identity={identity}
              actions={actions}
            />
          ) : null}
        </VideoContainer>
        <StatisticStyled isExpand={isExpand}>
          <StyledWrapperStatistic
            sx={{
              display: isExpand ? 'none' : 'flex',
              flexDirection: 'column'
            }}
          >
            <Box sx={{ flex: 1, minHeight: 0 }}>
              <ItemDetailInteractionInModal
                identity={identity}
                startFooterItems={startFooterItems}
                statisticDisplay={!is_streaming ? 'total_view' : ''}
                searchParams={searchParams}
                commentComposerProps={{
                  onSuccess: handleSuccess,
                  identity: parentReply?.parent_comment_identity || identity,
                  editorConfig: { disable_photo: is_streaming },
                  focus: focusState
                }}
                commentlistingComponent={
                  is_streaming && enableCommentApp ? (
                    <ListingComment
                      identity={identity}
                      streamKey={item?.stream_key}
                      setParentReply={handleReply}
                    />
                  ) : undefined
                }
              />
            </Box>
          </StyledWrapperStatistic>
        </StatisticStyled>
      </Root>
    </ErrorBoundary>
  );
}

export default connectItemView(LiveVideoViewDialog, actionCreators);
