import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import {
  Box,
  IconButton,
  Tab,
  Typography,
  styled,
  useMediaQuery
} from '@mui/material';
import React from 'react';
import SliderStory from './ContentDetailView/SliderStory';
import SliderStoryArchive from './SliderArchive/SliderStory';
import StatisticView from './ContentDetailView/StatisticView';
import { TabContext, TabList, TabPanel } from '@mui/lab';
import { LineIcon } from '@metafox/ui';
import CommentList from '@metafox/core/components/CommentList';
import { ItemInteractionTab, StoryItemProps } from '@metafox/story/types';
import { useStoryViewContext } from '../hooks';
import WapperItemInteraction from './WapperItemInteraction';

const name = 'ItemInteractionDetail';

const HeaderBlock = styled(Box, {
  name,
  slot: 'HeaderBlock',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  padding: theme.spacing(2),
  paddingTop: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const HeaderTitle = styled(Box, { name, slot: 'HeaderTitle' })(() => ({}));
const ContentBlock = styled(Box, { name, slot: 'ContentBlock' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary'),
    borderBottom: theme.mixins.border('secondary'),
    overflow: 'hidden'
  })
);

const CloseButton = styled(IconButton, { name })(() => ({
  marginLeft: 'auto',
  transform: 'translate(4px,0)',
  position: 'absolute',
  right: '16px'
}));

const TabPanelStyled = styled(TabPanel, {
  name,
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  padding: theme.spacing(1, 0),
  flex: 1,
  minHeight: 0,
  display: 'flex',
  flexDirection: 'column',
  ...(isMobile && {
    paddingBottom: 0
  })
}));

const CommentBlockStyled = styled(Box, {
  name,
  slot: 'CommentBlock',
  shouldForwardProp: props => props !== 'isMobile' && props !== 'isOwner'
})<{ isMobile?: boolean; isOwner?: boolean }>(
  ({ theme, isMobile, isOwner }) => ({
    flex: 1,
    minHeight: 0,
    ...(!isOwner && {
      borderTop: theme.mixins.border('secondary')
    })
  })
);

interface Props {
  item: StoryItemProps;
  open?: boolean;
  isMinHeight?: boolean;
  setOpen?: any;
  isArchive?: boolean;
}

const CommentListBlock = ({ item: _itemProp, isArchive = false }: any) => {
  const { jsxBackend, useActionControl, i18n, useGetItem } = useGlobal();

  const item = useGetItem(_itemProp?._identity);

  const {
    extra,
    related_comments,
    statistic,
    _identity: identity,
    is_owner
  } = item || {};

  const preFetchingComment = Object.values(
    item?.preFetchingComment || {}
  ).filter(item => item?.isLoading === true);

  const commentInputRef = React.useRef();

  const [handleAction] = useActionControl(identity, {
    commentFocused: false,
    menuOpened: false,
    commentOpened: true,
    commentInputRef
  });

  const viewMoreComments = (payload, meta) =>
    handleAction('comment/viewMoreComments', payload, meta);

  const CommentComposer = jsxBackend.get('CommentComposer');

  return (
    <>
      <CommentBlockStyled isOwner={is_owner}>
        {!statistic?.total_comment && !preFetchingComment?.length ? (
          <Typography variant="body1" color={'text.secondary'} sx={{ mt: 1 }}>
            {i18n.formatMessage(
              { id: 'description_no_comment_story' },
              { is_owner: is_owner ? 1 : 0 }
            )}
          </Typography>
        ) : (
          CommentList && (
            <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
              <CommentList
                id-tid="comment_list"
                handleAction={handleAction}
                data={related_comments}
                viewMoreComments={viewMoreComments}
                total_comment={statistic?.total_comment}
                total_reply={statistic?.total_reply}
                identity={identity}
                open
                isDetailPage
                disablePortalSort={false}
                showActionMenu={!isArchive}
              />
            </ScrollContainer>
          )
        )}
      </CommentBlockStyled>
      {extra?.can_comment && CommentComposer ? (
        <CommentComposer
          id-tid="comment_composer_story"
          identity={identity}
          open
          focus
          ref={commentInputRef}
        />
      ) : null}
    </>
  );
};

const OwnerBlock = React.memo(
  ({ item, isMobile, isArchive }: any) => {
    const { i18n, theme, usePageParams, useGetItems, getSetting } = useGlobal();
    const pageParams = usePageParams();
    const enableCommentApp = getSetting('comment');
    const { comment_id } = pageParams || {};

    const storyContext = useStoryViewContext();

    const storiesArchive = useGetItems(storyContext?.stories);

    const stories =
      isArchive && storiesArchive?.length ? storiesArchive : [item];

    const isMinHeight = useMediaQuery('(max-height:580px)');

    const [tabId, setTabId] = React.useState(
      comment_id ? ItemInteractionTab.Comments : ItemInteractionTab.Viewers
    );

    const handleChangeTab = (evt, newValue) => setTabId(newValue);

    return (
      <TabContext value={tabId}>
        {isMinHeight ? null : (
          <ContentBlock>
            {isArchive ? (
              <SliderStoryArchive stories={stories} item={item} />
            ) : (
              <SliderStory stories={stories} item={item} />
            )}
          </ContentBlock>
        )}
        <TabList
          onChange={handleChangeTab}
          aria-label="Images"
          sx={{ borderBottom: theme.mixins.border('secondary') }}
        >
          <Tab
            label={<b>{i18n.formatMessage({ id: 'viewers' })}</b>}
            value={ItemInteractionTab.Viewers}
          />
          {enableCommentApp ? (
            <Tab
              label={<b>{i18n.formatMessage({ id: 'comments' })}</b>}
              value={ItemInteractionTab.Comments}
            />
          ) : null}
        </TabList>
        {tabId === ItemInteractionTab.Viewers ? (
          <TabPanelStyled
            value={ItemInteractionTab.Viewers}
            isMobile={isMobile}
          >
            <StatisticView item={item} />
          </TabPanelStyled>
        ) : null}
        {enableCommentApp && tabId === ItemInteractionTab.Comments ? (
          <TabPanelStyled
            value={ItemInteractionTab.Comments}
            isMobile={isMobile}
          >
            <CommentListBlock item={item} isArchive={isArchive} />
          </TabPanelStyled>
        ) : null}
      </TabContext>
    );
  },
  (prev, next) => prev?.item?.id === next?.item?.id
);

function ItemInteractionDetail({
  item,
  open,
  setOpen,
  isMinHeight = false,
  isArchive = false
}: Props) {
  const { useGetItem, useSession, i18n, useIsMobile, useTheme, getSetting } =
    useGlobal();
  const theme = useTheme();
  const isMobile = useIsMobile(true);
  const enableCommentApp = getSetting('comment');

  const { user } = useSession();
  const userStory = useGetItem(item?.user);

  const isOwner = userStory?.id === user?.id;

  const handleClose = () => {
    setOpen(false);
  };

  if (isMobile) {
    if (!open) return null;

    return (
      <WapperItemInteraction
        setOpen={setOpen}
        open={open}
        isMinHeight={isMinHeight}
        sxProps={{ padding: theme.spacing(2), paddingTop: theme.spacing(2.5) }}
      >
        <HeaderBlock isOwner={isOwner}>
          <HeaderTitle>
            <Typography variant="h4" color={'text.primary'}>
              {i18n.formatMessage({
                id: isOwner ? 'story_details' : 'comments'
              })}
            </Typography>
          </HeaderTitle>
          <CloseButton
            size="small"
            onClick={handleClose}
            data-testid="buttonClose"
            role="button"
          >
            <LineIcon icon="ico-close" />
          </CloseButton>
        </HeaderBlock>
        {isOwner ? (
          <OwnerBlock item={item} isMobile={isMobile} isArchive={isArchive} />
        ) : (
          <CommentListBlock item={item} />
        )}
      </WapperItemInteraction>
    );
  }

  if (isOwner)
    return <OwnerBlock item={item} isMobile={isMobile} isArchive={isArchive} />;

  if (enableCommentApp) {
    return <CommentListBlock item={item} />;
  }

  return null;
}

export default ItemInteractionDetail;
