import { getTaggedFriendsPhotoSelector } from '@metafox/core/selectors/status';
import { FeedItemViewProps } from '@metafox/feed';
import ProfileLink from '@metafox/feed/components/FeedItemView/ProfileLink';
import FeedStatusView from '@metafox/feed/components/FeedStatus/FeedStatusView';
import {
  getItemSelector,
  GlobalState,
  useGlobal,
  useLoggedIn
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import {
  FeedStatistic,
  FromNow,
  LineIcon,
  PrivacyIcon,
  UserAvatar,
  Statistic,
  UserName,
  DotSeparator
} from '@metafox/ui';
import { Box, Divider, Skeleton } from '@mui/material';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import { useSelector } from 'react-redux';
import useStyles from './styles';
import TaggedFriends from './TaggedFriends';
import TaggedPlace from './TaggedPlace';
import CommentList from '@metafox/core/components/CommentList';

const name = 'ItemDetailInteractionInModal';

const ModalWrapper = styled(Box, {
  name,
  slot: 'root'
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  paddingBottom: 0,
  display: 'flex',
  flexFlow: 'column',
  height: '100%',
  justifyContent: 'space-between',
  minWidth: '420px',
  [theme.breakpoints.down('sm')]: {
    width: '100%',
    maxHeight: 'none',
    borderRadius: 0,
    minWidth: 'auto'
  }
}));

const ContentWrapper = styled('div', {
  name,
  slot: 'contentWrapper'
})(({ theme }) => ({
  flex: 1,
  minHeight: 0,
  display: 'flex',
  flexFlow: 'column',
  position: 'relative'
}));

const Header = styled('div', {
  name,
  slot: 'header'
})(({ theme }) => ({
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'row'
}));

const BodyWrapper = styled('div', {
  name,
  slot: 'body'
})(({ theme }) => ({
  flex: 1,
  minHeight: 0
}));

const Footer = styled('div', {
  name,
  slot: 'footer',
  shouldForwardProp: prop => prop !== 'maxHeight'
})<{ maxHeight?: number }>(({ theme, maxHeight }) => ({
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(0, 2),
  zIndex: 99,
  minHeight: theme.spacing(6),
  ...(maxHeight && {
    maxHeight
  }),
  [theme.breakpoints.down('sm')]: {
    position: 'unset',
    maxHeight: 'none'
  }
}));

const ContentStyled = styled('div', {
  name,
  slot: 'ContentStyled'
})(({ theme }) => ({
  marginBottom: theme.spacing(1)
}));

const PrivacyBlockStyled = styled('div', {
  name,
  slot: 'privacyBlock'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center',
  color: theme.palette.text.secondary
}));

const ProfileLinkStyled = styled(UserName, {
  name,
  slot: 'profileLink'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: theme.typography.fontWeightBold,
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.primary
}));

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeaderSubInfo = styled(Box, { name: 'HeaderSubInfo' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center'
}));

const WrapActionStyled = styled(Box, { name, slot: 'WrapAction' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row'
  })
);

const ItemDetailInteractionInModal = ({
  identity,
  item,
  user,
  loading,
  commentlistingComponent,
  startFooterItems,
  commentComposerProps,
  statisticDisplay,
  menuName,
  searchParams,
  isShowIClose = true
}: FeedItemViewProps & {
  loading?: boolean;
  commentlistingComponent?: React.ReactNode;
  startFooterItems: any;
  commentComposerProps?: Record<string, any>;
  statisticDisplay?: string;
  menuName?: string;
}) => {
  const commentInputRef = React.useRef();
  const wrapperModalRef = React.useRef(null);
  const headerRef = React.useRef(null);
  const footerRef = React.useRef(null);
  const {
    ReactionActButton,
    CommentReaction,
    CommentActButton,
    ShareActButton,
    ItemActionMenu,
    jsxBackend,
    useActionControl,
    i18n
  } = useGlobal();
  const [handleAction, state] = useActionControl(identity, {
    commentFocused: false,
    menuOpened: false,
    commentOpened: true,
    commentInputRef
  });
  const tagged_friends = useSelector((state: GlobalState) =>
    getTaggedFriendsPhotoSelector(state, item)
  );

  const owner = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.owner)
  );

  const classes = useStyles();
  const loggedIn = useLoggedIn();
  const viewMoreComments = (payload, meta) =>
    handleAction('comment/viewMoreComments', payload, meta);

  if (loading && !item?.extra) {
    // cheat check extra is mean have fetch data detail before
    return <LoadingSkeleton />;
  }

  const PendingPreview = jsxBackend.get('core.itemView.pendingReviewCard');

  if (!item || !user) return null;

  const { statistic, most_reactions_information, extra, location } = item;

  const CommentComposer = jsxBackend.get('CommentComposer');
  const IconCloseVideo = jsxBackend.get('core.ui.iconCloseVideo');

  return (
    <ModalWrapper>
      <ContentWrapper ref={wrapperModalRef}>
        <PendingPreview item={item} sx={{ borderRadius: 0 }} />
        <Header ref={headerRef}>
          <AvatarWrapper>
            <UserAvatar user={user} size={48} data-testid="author" />
          </AvatarWrapper>
          <HeaderSubInfo flex={1}>
            <div className={classes.headerHeadline}>
              <ProfileLinkStyled
                to={user.link}
                user={user}
                data-testid="headline"
              />
              {owner?.resource_name !== user?.resource_name && owner && (
                <HeadlineSpan>
                  {i18n.formatMessage(
                    {
                      id: 'to_parent_user'
                    },
                    {
                      icon: () => (
                        <LineIcon
                          icon="ico-caret-right"
                          className={classes.caretIcon}
                        />
                      ),
                      parent_user: () => (
                        <ProfileLink
                          user={owner}
                          className={classes.profileLink}
                        />
                      )
                    }
                  )}
                </HeadlineSpan>
              )}
            </div>
            <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
              <FromNow value={item.creation_date} data-testid="creationDate" />
              {statisticDisplay ? (
                <Statistic
                  values={item.statistic}
                  display={statisticDisplay}
                  component={'span'}
                  skipZero={false}
                />
              ) : null}
              <PrivacyIcon
                value={item.privacy}
                item={item?.privacy_detail}
                data-testid="iconPrivacy"
              />
            </DotSeparator>
          </HeaderSubInfo>
          <WrapActionStyled>
            <ItemActionMenu
              identity={identity}
              state={state}
              handleAction={handleAction}
              {...(menuName ? { menuName } : null)}
            />
            {isShowIClose && IconCloseVideo ? <IconCloseVideo /> : null}
          </WrapActionStyled>
        </Header>
        <BodyWrapper>
          <Box
            sx={{
              maxHeight: '100%',
              display: 'flex',
              flexDirection: 'column',
              height: '100%'
            }}
          >
            <ScrollContainer autoHide autoHeight autoHeightMax={'100%'}>
              <Box px={2}>
                <Box
                  sx={{
                    '&:not(:empty)': { marginBottom: theme => theme.spacing(2) }
                  }}
                >
                  {item?.text || item?.description ? (
                    <ContentStyled>
                      <FeedStatusView
                        status={item?.text ?? item?.description}
                      />
                    </ContentStyled>
                  ) : null}
                  {tagged_friends?.length || location?.address ? (
                    <HeadlineSpan className={classes.statusRoot}>
                      {tagged_friends?.length ? (
                        <TaggedFriends
                          item_type={item.resource_name}
                          item_id={item.id}
                          total={tagged_friends.length}
                          users={tagged_friends}
                          className={classes.profileLink}
                        />
                      ) : null}
                      {location?.address ? (
                        <Box component="span" ml={0.5}>
                          {i18n.formatMessage(
                            {
                              id: 'at_tagged_place'
                            },
                            {
                              name: location.address,
                              bold: () => <TaggedPlace place={location} />
                            }
                          )}
                        </Box>
                      ) : null}
                    </HeadlineSpan>
                  ) : null}
                </Box>
                <FeedStatistic
                  id-tid="statistic"
                  handleAction={handleAction}
                  identity={identity}
                  reactions={most_reactions_information}
                  statistic={statistic}
                />
                {CommentReaction ? (
                  <CommentReaction>
                    {ReactionActButton && extra?.can_like && (
                      <ReactionActButton
                        id-tid="reaction"
                        reacted={item.user_reacted}
                        identity={identity}
                        handleAction={handleAction}
                      />
                    )}
                    {CommentActButton && extra?.can_comment && (
                      <CommentActButton
                        id-tid="comment"
                        identity={identity}
                        handleAction={handleAction}
                      />
                    )}
                    {ShareActButton && extra?.can_share && (
                      <ShareActButton
                        handleAction={handleAction}
                        id-tid="share"
                        identity={identity}
                      />
                    )}
                  </CommentReaction>
                ) : null}
                <Divider />
                {commentlistingComponent ? (
                  commentlistingComponent
                ) : (
                  <CommentList
                    id-tid="comment_list"
                    handleAction={handleAction}
                    data={item.related_comments}
                    viewMoreComments={viewMoreComments}
                    total_comment={statistic?.total_comment}
                    total_reply={statistic?.total_reply}
                    identity={identity}
                    open={state?.commentOpened}
                    isDetailPage
                    searchParams={searchParams}
                  />
                )}
              </Box>
            </ScrollContainer>
          </Box>
        </BodyWrapper>
        <Footer ref={footerRef}>
          {startFooterItems ? jsxBackend.render(startFooterItems) : null}
          {loggedIn && extra?.can_comment && CommentComposer ? (
            <CommentComposer
              id-tid="comment_composer"
              identity={identity}
              open={state?.commentOpened}
              focus={state.commentFocused}
              {...commentComposerProps}
            />
          ) : null}
        </Footer>
      </ContentWrapper>
    </ModalWrapper>
  );
};

const LoadingSkeleton = () => {
  return (
    <ModalWrapper>
      <Box>
        <Header>
          <Box pr={1.5}>
            <Skeleton variant="circular" width={40} height={40} />
          </Box>
          <Box py={0.5} flex={1}>
            <div>
              <Skeleton variant="text" component="div" />
            </div>
            <PrivacyBlockStyled>
              <Skeleton variant="text" width={120} />
            </PrivacyBlockStyled>
          </Box>
        </Header>
        <Box px={2} mt={4}>
          <Box sx={{ display: 'flex', margin: '0 -8px' }}>
            <Box p={1} flex={1}>
              <Skeleton variant="rounded" height={32} />
            </Box>
            <Box p={1} flex={1}>
              <Skeleton variant="rounded" height={32} />
            </Box>
            <Box p={1} flex={1}>
              <Skeleton variant="rounded" height={32} />
            </Box>
          </Box>
        </Box>
        <Box px={2} mt={4}>
          <Skeleton variant="text" />
          <Skeleton variant="text" />
          <Skeleton variant="text" />
        </Box>
      </Box>
    </ModalWrapper>
  );
};

ItemDetailInteractionInModal.LoadingSkeleton = LoadingSkeleton;

export default ItemDetailInteractionInModal;
