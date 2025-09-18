import { useGlobal } from '@metafox/framework';
import { FromNow, UserAvatar, ActButton, UserName } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';
import Content from './Content';
import EditContent from './EditContent';
import Reply from './Reply';

const name = 'Comment';

const ItemOuter = styled('div', {
  name,
  slot: 'itemOuter',
  shouldForwardProp: prop => prop !== 'isLoading'
})<{ isLoading: boolean }>(({ theme, isLoading }) => ({
  display: 'flex',
  ...(isLoading && {
    opacity: 0.6,
    pointerEvents: 'none'
  }),
  '&:hover .itemActionMenu': {
    visibility: 'visible',
    marginLeft: theme.spacing(0.5)
  }
}));
const ItemInner = styled('div', { name, slot: 'itemInner' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  minWidth: 0,
  wordBreak: 'break-word'
}));
const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1)
  })
);
const ItemName = styled('div', { name, slot: 'ItemName' })(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  marginBottom: theme.spacing(0.5)
}));
const UserNameStyled = styled(UserName, { name, slot: 'userName' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    maxWidth: '100%',
    fontWeight: 'bold'
  })
);
const FromNowStyled = styled(FromNow, { name, slot: 'FromNowStyled' })(
  ({ theme }) => ({
    display: 'flex',
    color: theme.palette.text.secondary,
    whiteSpace: 'nowrap',
    alignItems: 'flex-end',
    marginLeft: theme.spacing(1)
  })
);
const ReactionsWrapper = styled('div', { name, slot: 'ReactionsWrapper' })(
  ({ theme }) => ({
    display: 'inline-flex',
    alignItems: 'center',
    height: theme.spacing(4)
  })
);

const BoxWrapper = styled(Box, { name, slot: 'BoxMessageWrapper' })<{
  highlight?: boolean;
}>(({ theme, highlight }) => ({
  position: 'relative',
  '&:before': {
    content: '""',
    position: 'absolute',
    left: '-4px',
    top: '-4px',
    right: '-4px',
    bottom: '-4px',
    borderRadius: theme.shape.borderRadius,
    transition: 'background 300ms ease',
    background: 'none',
    pointerEvents: 'none',
    ...(highlight && {
      background:
        theme.palette.mode === 'dark'
          ? theme.palette.grey[600]
          : theme.palette.grey[100]
    })
  }
}));

type CommentItemProps = Record<string, any>;
export default function Comment({
  item,
  user: userProps,
  itemLive,
  identity,
  extra_data,
  parent_user,
  setParentReply,
  identityResource,
  handleAction,
  state,
  actions
}: CommentItemProps) {
  // const item = itemLive;
  const {
    i18n,
    useTheme,
    ItemActionMenu,
    ReactionResult,
    ReactionActButton,
    ReplyActButton,
    RemovePreviewActButton,
    getSetting,
    usePageParams,
    HistoryEditedCommentButton,
    useLoggedIn
  } = useGlobal();
  const theme = useTheme();

  const user = userProps || itemLive?.user;
  const { comment_id } = usePageParams();
  const isLoggedIn = useLoggedIn();

  const [highlight, setHighlight] = React.useState(false);
  const [isPreviewHidden, setIsPreviewHidden] = React.useState(false);

  React.useEffect(() => {
    if (comment_id) {
      setHighlight(parseInt(comment_id) === item?.id);
      setTimeout(() => {
        setHighlight(false);
      }, 2000);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [comment_id]);

  const handlePreviewHiddenComment = () => {
    setIsPreviewHidden(prev => !prev);
  };

  if (!item || !user) return null;

  const isThreadDisplay = getSetting('comment.enable_thread');

  const {
    text,
    editText,
    creation_date,
    most_reactions_information,
    statistic,
    is_edited,
    is_hidden,
    parent
  } = item;

  const handleReply = dataReply => {
    const { replyUser, replyComment } = dataReply || {};

    if (replyUser) {
      setParentReply({
        user_full_name: replyUser?.full_name,
        parent_comment_identity: replyComment?._identity
      });
    }
  };

  const showRemovePreviewButton =
    extra_data?.extra_type === 'link' && item?.extra?.can_remove_link_preview;

  return (
    <div
      data-testid="comment"
      id={`comment-${item.id}`}
      data-author={user.full_name}
    >
      <Box pt={2}>
        {parent ? <Reply item={itemLive?.parent} /> : null}
        {item.isEditing ? (
          <EditContent
            text={editText}
            extra_data={extra_data}
            handleAction={handleAction}
            identity={identity}
            actions={actions}
          />
        ) : (
          <ItemOuter isLoading={item.isLoading}>
            <AvatarWrapper>
              <UserAvatar user={user as any} size={32} />
            </AvatarWrapper>
            <ItemInner>
              <BoxWrapper p={0} borderRadius={2} highlight={!!highlight}>
                <Box sx={{ position: 'relative', zIndex: 2 }}>
                  <ItemName>
                    <UserNameStyled to={`/user/${user.id}`} user={user} />
                    <FromNowStyled value={creation_date} shorten />
                    {HistoryEditedCommentButton && is_edited ? (
                      <HistoryEditedCommentButton
                        data-testid="historyCommentButton"
                        minimize
                        sx={{ marginLeft: '8px', textTransform: 'capitalize' }}
                        identity={identity}
                        handleAction={handleAction}
                      />
                    ) : null}
                  </ItemName>
                  <Content
                    isHidden={is_hidden && !isPreviewHidden}
                    text={text}
                    extra_data={!item?.hideExtraData ? extra_data : null}
                  />
                </Box>
              </BoxWrapper>
              {isLoggedIn && (
                <ReactionsWrapper className={'dotSeparators'}>
                  {is_hidden ? (
                    <Tooltip
                      componentsProps={{
                        tooltip: { sx: { maxWidth: '210px !important' } }
                      }}
                      title={i18n.formatMessage({
                        id: 'preview_hidden_comment_tooltip'
                      })}
                    >
                      <span>
                        <ActButton
                          data-testid="previewHiddenButton"
                          onClick={handlePreviewHiddenComment}
                          label={i18n.formatMessage({ id: 'preview' })}
                          color={
                            isPreviewHidden ? theme.palette.primary.main : ''
                          }
                          minimize
                        />
                      </span>
                    </Tooltip>
                  ) : (
                    <>
                      {ReactionActButton && item?.extra?.can_like ? (
                        <ReactionActButton
                          minimize
                          reacted={item.user_reacted}
                          identity={identity}
                          handleAction={handleAction}
                        />
                      ) : null}
                      {ReplyActButton &&
                      isThreadDisplay &&
                      item?.extra?.can_comment ? (
                        <ReplyActButton
                          minimize
                          identity={identity}
                          openReplyComposer={handleReply}
                          handleAction={handleAction}
                        />
                      ) : null}
                      {showRemovePreviewButton ? (
                        <RemovePreviewActButton
                          actions={actions}
                          identity={identity}
                          minimize
                        />
                      ) : null}
                      {ReactionResult ? (
                        <ReactionResult
                          size="sm"
                          identity={identity}
                          handleAction={handleAction}
                          data={most_reactions_information}
                          total={statistic?.total_like}
                        />
                      ) : null}
                    </>
                  )}
                  <ItemActionMenu
                    identity={identity}
                    state={state}
                    handleAction={handleAction}
                    size="smaller"
                    sx={{ visibility: 'hidden' }}
                    className={'itemActionMenu'}
                  />
                </ReactionsWrapper>
              )}
            </ItemInner>
          </ItemOuter>
        )}
      </Box>
    </div>
  );
}
