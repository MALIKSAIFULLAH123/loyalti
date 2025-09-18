import { Link, useGlobal } from '@metafox/framework';
import {
  ActButton,
  FromNow,
  LineIcon,
  RoleLabel,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Box, Tooltip, styled } from '@mui/material';
import React from 'react';
import { ReplyItemProps } from '../../types';
import Content from './Content';
import EditContent from './EditContent';
import { MenuItemPendding } from './Comment';

export type CommentItemState = {
  menuOpened: boolean;
  commentOpened: boolean;
};

const name = 'Reply';

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
const ItemName = styled('div', { name, slot: 'itemName' })(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  marginBottom: theme.spacing(0.5),
  alignItems: 'center'
}));
const AvatarWrapper = styled('div', { name, slot: 'avatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1)
  })
);
const UserNameStyled = styled(UserName, { name, slot: 'userName' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    maxWidth: '100%',
    fontWeight: 'bold'
  })
);
const FromNowStyled = styled(FromNow, { name, slot: 'FromNow' })(
  ({ theme }) => ({
    display: 'flex',
    color: theme.palette.text.secondary,
    whiteSpace: 'nowrap',
    alignItems: 'flex-end',
    marginLeft: theme.spacing(1),
    '&:before': {
      display: 'none'
    }
  })
);
const Actions = styled('div', { name, slot: 'actions' })(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  height: theme.spacing(4),
  '&:empty': {
    height: theme.spacing(2)
  }
}));

const BoxWrapper = styled(Box, {
  name,
  slot: 'BoxMessageWrapper',
  shouldForwardProp: props => props !== 'highlight'
})<{
  highlight?: boolean;
}>(({ theme, highlight }) => ({
  position: 'relative',
  padding: 0,
  ...(highlight && {
    '&:before': {
      content: '""',
      position: 'absolute',
      left: '-4px',
      top: '-4px',
      right: '-4px',
      bottom: '-4px',
      borderRadius: theme.shape.borderRadius,
      transition: 'background 300ms ease',
      pointerEvents: 'none',
      background:
        theme.palette.mode === 'dark'
          ? theme.palette.grey[600]
          : theme.palette.grey[100]
    }
  })
}));

const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name
})(({ theme }) => ({
  display: 'inline-flex',
  '&:before': {
    display: 'none'
  },
  '&>span': {
    marginRight: theme.spacing(1),
    marginLeft: theme.spacing(1),
    marginBottom: theme.spacing(0)
  }
}));

export default function Reply({
  item,
  user,
  openReplyComposer,
  state,
  actions,
  extra_data,
  handleAction,
  identity,
  parent_user
}: ReplyItemProps) {
  const {
    ItemActionMenu,
    ReactionResult,
    ReactionActButton,
    ReplyActButton,
    RemovePreviewActButton,
    usePageParams,
    getSetting,
    HistoryEditedCommentButton,
    i18n,
    useTheme
  } = useGlobal();
  const theme = useTheme();
  const isThreadDisplay = getSetting('comment.enable_thread');
  const { comment_id, module_name } = usePageParams();
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

  if (!item) return null;

  const handlePreviewHiddenComment = () => {
    setIsPreviewHidden(prev => !prev);
  };

  const {
    text,
    editText,
    creation_date,
    most_reactions_information,
    statistic,
    link,
    is_edited,
    role_label,
    is_pending,
    is_hidden
  } = item;

  const showRemovePreviewButton =
    extra_data?.extra_type === 'link' && item?.extra?.can_remove_link_preview;

  return (
    <div>
      <Box pt={1}>
        {item.isEditing ? (
          <EditContent
            text={editText}
            extra_data={extra_data}
            handleAction={handleAction}
            identity={identity}
            isReply
            parent_user={parent_user}
            actions={actions}
          />
        ) : (
          <ItemOuter isLoading={item.isLoading}>
            <AvatarWrapper>
              <UserAvatar user={user} size={32} noStory />
            </AvatarWrapper>
            <ItemInner>
              <BoxWrapper borderRadius={2} highlight={!!highlight}>
                <Box sx={{ position: 'relative', zIndex: 2 }}>
                  <ItemName className={'dotSeparators'}>
                    <UserNameStyled to={`/user/${user.id}`} user={user} />
                    {(module_name === parent_user?.module_name ||
                      !parent_user) &&
                      role_label && (
                        <RoleLabel text={role_label} sx={{ ml: 1 }} />
                      )}
                    <Link color="inherit" to={link}>
                      <FromNowStyled value={creation_date} shorten />
                    </Link>
                    {is_pending ? (
                      <FlagWrapper>
                        <Tooltip
                          title={i18n.formatMessage({ id: 'pending' })}
                          placement="top"
                        >
                          <LineIcon icon="ico-clock-o" />
                        </Tooltip>
                      </FlagWrapper>
                    ) : null}
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
              <Actions className={'dotSeparators'}>
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
                        openReplyComposer={openReplyComposer}
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
                {is_pending ? (
                  <MenuItemPendding
                    identity={identity}
                    handleAction={handleAction}
                  />
                ) : (
                  <ItemActionMenu
                    identity={identity}
                    state={state}
                    handleAction={handleAction}
                    size="smaller"
                    sx={{ visibility: 'hidden' }}
                    className={'itemActionMenu'}
                  />
                )}
              </Actions>
            </ItemInner>
          </ItemOuter>
        )}
      </Box>
    </div>
  );
}
