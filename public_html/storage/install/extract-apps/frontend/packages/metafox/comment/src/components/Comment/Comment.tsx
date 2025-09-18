import {
  GlobalState,
  Link,
  getResourceMenuSelector,
  useGlobal,
  useLocation,
  useSession
} from '@metafox/framework';
import {
  FromNow,
  LineIcon,
  UserAvatar,
  ActButton,
  UserName,
  RoleLabel
} from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';
import { CommentItemProps } from '../../types';
import Content from './Content';
import EditContent from './EditContent';
import { assign } from 'lodash';
import { useSelector } from 'react-redux';
import { RESOURCE_COMMENT } from '@metafox/comment/constant';
import { filterShowWhen } from '@metafox/utils';
import ReplyCommentList from '@metafox/comment/components/ReplyCommentList';

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
  marginBottom: theme.spacing(0.5),
  alignItems: 'center'
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

const ReplyWrapper = styled('div', { name, slot: 'ReplyWrapper' })(
  ({ theme }) => ({
    paddingLeft: theme.spacing(5),
    '& > div': {
      position: 'relative',
      '&:before': {
        content: '""',
        width: 12,
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        position: 'absolute',
        left: theme.spacing(-3),
        top: theme.spacing(3)
      }
    }
  })
);

const BoxWrapper = styled(Box, {
  name,
  slot: 'BoxMessageWrapper',
  overridesResolver(props, styles) {
    return [styles.boxMessageWrapper];
  },
  shouldForwardProp: props => props !== 'highlight'
})<{
  highlight?: boolean;
}>(({ theme, highlight }) => ({
  position: 'relative',
  marginRight: 0,
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
  '&>span': {
    marginRight: theme.spacing(1),
    marginLeft: theme.spacing(1),
    marginBottom: theme.spacing(0)
  }
}));

interface IPropMenu {
  item?: any;
  identity: string;
  handleAction?: any;
}

const ItemActionMenuPendding = ({ item, identity }: IPropMenu): JSX.Element => {
  const { dispatch } = useGlobal();

  const { items: menu } = useSelector(
    (state: GlobalState) =>
      getResourceMenuSelector(
        state,
        RESOURCE_COMMENT,
        RESOURCE_COMMENT,
        'extraActionMenu'
      ) || {}
  );

  const menuPenddingFilter = filterShowWhen(menu || [], {
    item
  });

  return menuPenddingFilter.map((item, index) => (
    <ActButton
      key={index}
      minimize
      data-testid={`${item.name}Button`}
      aria-label={item.label}
      onClick={() =>
        dispatch({
          type: item.value,
          payload: {
            identity
          }
        })
      }
      label={item.label}
    />
  )) as any;
};

export const MenuItemPendding = ({
  identity,
  handleAction
}: IPropMenu): JSX.Element => {
  const { dispatch, ItemActionMenu, useGetItem } = useGlobal();

  const item = useGetItem(identity);

  const user = useGetItem(item?.user);

  const { user: authUser } = useSession();
  const isOwner = authUser?.id === user?.id;

  const { items: menu } = useSelector(
    (state: GlobalState) =>
      getResourceMenuSelector(
        state,
        RESOURCE_COMMENT,
        RESOURCE_COMMENT,
        'itemActionMenu'
      ) || {}
  );

  const menuItemFilter = filterShowWhen(menu || [], {
    item
  });

  if (!menuItemFilter.length) {
    return <ItemActionMenuPendding item={item} identity={identity} />;
  }

  const menuItemFirst = menuItemFilter[0];

  const menuItemOther = isOwner ? menuItemFilter.splice(1) : menuItemFilter;

  return (
    <>
      <ItemActionMenuPendding item={item} identity={identity} />
      {isOwner && menuItemFirst && (
        <ActButton
          minimize
          data-testid={`${menuItemFirst.name}Button`}
          aria-label={menuItemFirst.label}
          onClick={() =>
            dispatch({
              type: menuItemFirst.value,
              payload: {
                identity
              }
            })
          }
          label={menuItemFirst.label}
        />
      )}

      <ItemActionMenu
        identity={identity}
        items={menuItemOther}
        handleAction={handleAction}
        size="smaller"
        sx={{ visibility: 'hidden' }}
        className={'itemActionMenu'}
      />
    </>
  );
};

export default function Comment({
  item,
  user,
  identity,
  handleAction,
  state,
  extra_data,
  actions,
  parent_user,
  identityResource,
  searchParams = {},
  sortType,
  showActionMenu = true
}: CommentItemProps) {
  const location = useLocation();
  const isModalPage = location.state?.asModal;
  const {
    i18n,
    jsxBackend,
    useTheme,
    ItemActionMenu,
    ReactionResult,
    ReactionActButton,
    ReplyActButton,
    RemovePreviewActButton,
    getSetting,
    usePageParams,
    HistoryEditedCommentButton,
    useLoggedIn,
    useIsMobile
  } = useGlobal();
  const isMobile = useIsMobile();
  const theme = useTheme();
  const pageParams = usePageParams();
  const { comment_id, module_name } = assign(searchParams, pageParams);
  const isLoggedIn = useLoggedIn();
  const itemRef = React.useRef<HTMLDivElement>();

  const CommentComposer = jsxBackend.get('CommentComposer');
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

  React.useEffect(() => {
    if (isMobile && item?.isNew)
      itemRef.current?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest'
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

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
    link,
    is_edited,
    is_hidden,
    role_label,
    is_pending
  } = item;

  const showRemovePreviewButton =
    extra_data?.extra_type === 'link' && item?.extra?.can_remove_link_preview;

  return (
    <div
      data-testid="comment"
      id={`comment-${item.id}`}
      data-author={user.full_name}
      ref={itemRef}
    >
      <Box pt={1}>
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
              <UserAvatar user={user as any} size={32} noStory />
            </AvatarWrapper>
            <ItemInner>
              <BoxWrapper borderRadius={2} highlight={!!highlight}>
                <Box sx={{ position: 'relative', zIndex: 2 }}>
                  <ItemName>
                    <UserNameStyled to={`/user/${user.id}`} user={user} />
                    {(module_name === parent_user?.module_name ||
                      !parent_user) &&
                      role_label && (
                        <RoleLabel text={role_label} sx={{ ml: 1 }} />
                      )}
                    <Link
                      color="inherit"
                      to={link}
                      asModal={isModalPage && link.includes(location.pathname)}
                    >
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
                  </ItemName>
                  <Content
                    isHidden={is_hidden && !isPreviewHidden}
                    text={text}
                    identity={identity}
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
                          openReplyComposer={actions.openReplyComposer}
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
                  {HistoryEditedCommentButton && is_edited ? (
                    <HistoryEditedCommentButton
                      data-testid="historyCommentButton"
                      minimize
                      sx={{ textTransform: 'capitalize' }}
                      identity={identity}
                      handleAction={handleAction}
                    />
                  ) : null}
                  {showActionMenu ? (
                    is_pending ? (
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
                    )
                  ) : null}
                </ReactionsWrapper>
              )}
            </ItemInner>
          </ItemOuter>
        )}
      </Box>
      <ReplyCommentList
        identity={identity}
        parent_user={parent_user}
        actions={actions}
        sortType={sortType}
        handleAction={handleAction}
        comment_id={comment_id}
      />
      {!item.parent_id && state.commentOpened && CommentComposer ? (
        <ReplyWrapper>
          <CommentComposer
            open={state.commentOpened}
            focus={state.commentFocused}
            replyUser={state.replyUser}
            margin="dense"
            identity={identity}
            identityResource={identityResource}
            isReply
            parentUser={parent_user}
          />
        </ReplyWrapper>
      ) : null}
    </div>
  );
}
