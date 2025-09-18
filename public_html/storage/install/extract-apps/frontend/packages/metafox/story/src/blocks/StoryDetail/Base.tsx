import { connectItem, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { ItemInteractionModal, PlayStoryView } from '@metafox/story/components';
import { Box, IconButton, Tooltip, Typography, styled } from '@mui/material';
import { StoryViewContext, initStateStory } from '@metafox/story/context';
import { reducerStoryView } from '@metafox/story/context/reducerStoryView';
import PenddingView from '../MainViewHome/PenddingView';
import { useStoryViewContext } from '@metafox/story/hooks';
import { StoryItemProps } from '@metafox/story/types';
import { LineIcon } from '@metafox/ui';

const name = 'StoryDetail';

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

export const ContentDetailBlock = ({ item, isOwner = false }: ContentProps) => {
  const storyContent = useStoryViewContext();
  const { isReady } = storyContent || {};

  if (!item?.in_process && !isReady && window.navigator?.userActivation) {
    if (!window.navigator?.userActivation?.hasBeenActive)
      return <PenddingView isDetailPage isOwner={isOwner} />;
  }

  return <PlayStoryView story={item} isModal={false} isOwner={isOwner} />;
};

function StoryDetail({ item }: { item: StoryItemProps }) {
  const { useGetItem, useSession, i18n, useIsMobile, navigate, getSetting } =
    useGlobal();
  const isMobile = useIsMobile(true);
  const enableCommentApp = getSetting('comment');

  const [state, fire] = React.useReducer(reducerStoryView, {
    ...initStateStory
  });

  React.useEffect(() => {
    fire({
      type: 'setReady',
      payload: false
    });
  }, []);

  const { user } = useSession();
  const userStory = useGetItem(item?.user);

  if (!item) return null;

  const isOwner = userStory?.id === user?.id;

  const handleClose = () => {
    navigate('/');
  };

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <StoryViewContext.Provider value={{ ...state, fire }}>
          <RootStyled>
            <DetailStory isOwner={isOwner} isMobile={isMobile}>
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
              <ContentDetailBlock item={item} isOwner={isOwner} />
            </DetailStory>
            {enableCommentApp ? (
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
                    <ItemInteractionModal item={item} />
                  </ContentBlock>
                </StyledWrapperStatistic>
              </DetailStatistic>
            ) : null}
          </RootStyled>
        </StoryViewContext.Provider>
      </BlockContent>
    </Block>
  );
}

export default connectItem(StoryDetail);
