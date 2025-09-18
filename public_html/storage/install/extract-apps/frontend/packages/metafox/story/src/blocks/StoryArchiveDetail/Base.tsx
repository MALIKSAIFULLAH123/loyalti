import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import {
  ArchiveStoryView,
  ItemInteractionModal
} from '@metafox/story/components';
import { Box, IconButton, Tooltip, Typography, styled } from '@mui/material';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { useStoryViewContext } from '@metafox/story/hooks';
import { PauseStatus, StoryItemProps } from '@metafox/story/types';
import { LineIcon } from '@metafox/ui';
import connectStoryArchive from '@metafox/story/hooks/connectStoryArchive';
import { reducerArchiveView } from '@metafox/story/context/reducerArchiveView';
import { isEmpty } from 'lodash';
import PenddingView from '../MainViewHome/PenddingView';

const name = 'StoryArchiveDetail';

const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled'
})<{}>(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  padding: '0 !important',
  height: '100%',
  display: 'flex',
  overflowX: 'hidden',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column'
  }
}));

const DetailStory = styled('div', {
  name,
  slot: 'DetailStory',
  shouldForwardProp: props => props !== 'isOwner' && props !== 'isMobile'
})<{ isOwner?: boolean; isMobile?: boolean }>(
  ({ theme, isOwner, isMobile }) => ({
    position: 'relative',
    backgroundColor: '#000',
    width: '100%',
    overflow: 'hidden',
    flex: 1,
    minWidth: 0,
    padding: theme.spacing(1.5, 0),
    '& iframe': {
      width: '100%',
      height: '100%'
    },
    [theme.breakpoints.down('md')]: {
      width: '100%',
      height: 'auto',
      borderRadius: 0,
      overflow: 'initial'
    },
    ...((isMobile || !isOwner) && {
      paddingBottom: 0
    })
  })
);

const DetailStatistic = styled('div', {
  name,
  slot: 'DetailStatistic'
})(({ theme }) => ({
  height: '100%',
  width: '420px',
  [theme.breakpoints.down('md')]: {
    width: '100%'
  },
  [theme.breakpoints.down('xs')]: {
    width: '100%',
    height: '400px'
  }
}));

const StyledWrapperStatistic = styled(Box, { name, slot: 'WrapperStatistic' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    height: '100%',
    padding: theme.spacing(2),
    paddingTop: theme.spacing(2.5),
    paddingBottom: 0
  })
);

const HeaderBlock = styled(Box, { name, slot: 'HeaderBlock' })(({ theme }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    overflow: 'hidden',
    display: 'flex',
    flexDirection: 'column',
    flex: 1,
    minHeight: 0
  })
);

const ActionBar = styled('div', { name, slot: 'actionBar' })(({ theme }) => ({
  position: 'absolute',
  right: 0,
  top: 0,
  padding: theme.spacing(1),
  display: 'flex',
  justifyContent: 'space-between',
  zIndex: 1,
  alignItems: 'center'
}));

const IconButtonStyled = styled(IconButton, { name, slot: 'TagFriend' })(
  ({ theme }) => ({
    color: '#fff !important',
    width: 32,
    height: 32,
    fontSize: theme.mixins.pxToRem(15)
  })
);

interface ContentProps {
  item: StoryItemProps;
  isOwner: boolean;
}

interface Props {
  identity: string;
  total?: any;
  prevDate?: string;
  nextDate?: string;
  stories?: string[];
  story_id?: string;
  shouldPreload?: any;
  pagingId?: string;
  positionStory?: string;
  date?: string;
  indexStoryActive?: string;
  [key: string]: any;
}

export const ContentDetailArchiveBlock = (props: ContentProps) => {
  const { item, isOwner = false } = props || {};
  const storyContent = useStoryViewContext();
  const { isReady } = storyContent || {};

  if (!item?.in_process && !isReady && window.navigator?.userActivation) {
    if (!window.navigator?.userActivation?.hasBeenActive)
      return <PenddingView isDetailPage isOwner={isOwner} />;
  }

  return <ArchiveStoryView story={item} isModal={false} isOwner={isOwner} />;
};

function StoryDetail(props: Props) {
  const {
    useGetItem,
    useSession,
    i18n,
    useIsMobile,
    dispatch,
    goSmartBack,
    usePageParams
  } = useGlobal();
  const pageParams = usePageParams();
  const {
    identity,
    stories,
    story_id,
    total: totalProps,
    nextDate,
    prevDate,
    shouldPreload: shouldPreloadProps,
    indexStoryActive: indexStoryActiveProps,
    date,
    positionStory: positionStoryProps,
    pagingId
  } = props || {};
  const isMobile = useIsMobile(true);
  const { user } = useSession();

  const [state, fire] = React.useReducer(reducerArchiveView, {
    ...initStateStory
  });

  const refBlur = React.useRef(false);
  const refBlurTimeout = React.useRef();

  const { identityStoryActive, pauseStatus } = state || {};

  const itemInit: any = useGetItem(identity);

  const item: any = useGetItem(identityStoryActive) || itemInit;

  const userStory = useGetItem(item?.user);

  React.useEffect(() => {
    if (!stories?.length) return;

    fire({
      type: 'setInit',
      payload: {
        isReady: false,
        stories,
        story_id,
        total: totalProps,
        nextDate,
        prevDate,
        date,
        positionStory: positionStoryProps,
        pagingId
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (isEmpty(itemInit)) return;

    fire({
      type: 'setInit',
      payload: {
        indexStoryActive: indexStoryActiveProps,
        identityStoryActive: identity
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemInit]);

  React.useEffect(() => {
    if (!pageParams?.id || !shouldPreloadProps) return;

    dispatch({
      type: 'story/story_archive/LOAD',
      payload: {
        user_id: pageParams?.user_id,
        date,
        direction: shouldPreloadProps
      },
      meta: {
        onSuccess: handleSuccessLoadMore
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

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
        pagingId
      }
    });
  };

  if (!userStory) return null;

  const isOwner = userStory?.id === user?.id;

  const handleClose = () => {
    goSmartBack();
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

  if (!item) return null;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <StoryViewContext.Provider value={{ ...state, fire }}>
          <RootStyled>
            <DetailStory
              isOwner={isOwner}
              isMobile={isMobile}
              onBlur={onBlurContainerView}
              onMouseEnter={onMouseEnterContainerView}
              onMouseLeave={onMouseLeaveContainerView}
              tabIndex={0}
            >
              {isMobile ? null : (
                <ActionBar>
                  <Box>
                    <Tooltip title={i18n.formatMessage({ id: 'close' })}>
                      <IconButtonStyled onClick={handleClose}>
                        <LineIcon icon="ico-close" color="white" />
                      </IconButtonStyled>
                    </Tooltip>
                  </Box>
                </ActionBar>
              )}
              <ContentDetailArchiveBlock item={item} isOwner={isOwner} />
            </DetailStory>
            <DetailStatistic>
              <StyledWrapperStatistic>
                <HeaderBlock>
                  <HeaderTitle>
                    <Typography variant="h4" color={'text.primary'}>
                      {i18n.formatMessage({
                        id: isOwner ? 'story_details' : 'comments'
                      })}
                    </Typography>
                  </HeaderTitle>
                </HeaderBlock>
                <ContentBlock>
                  <ItemInteractionModal item={item} isArchive />
                </ContentBlock>
              </StyledWrapperStatistic>
            </DetailStatistic>
          </RootStyled>
        </StoryViewContext.Provider>
      </BlockContent>
    </Block>
  );
}

export default connectStoryArchive(StoryDetail);
