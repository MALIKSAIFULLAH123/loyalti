import { useGlobal, Link } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { NotificationItemProps } from '@metafox/notification/types';
import {
  FromNow,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemView,
  UserAvatar,
  ItemAction,
  LineIcon
} from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import { alpha } from '@mui/system/colorManipulator';
import React from 'react';

const ActionButtonStyled = styled(IconButton, {
  shouldForwardProp: prop => {
    return prop !== 'isRead';
  }
})<{ isRead?: boolean }>(({ theme, isRead }) => ({
  position: 'relative',
  zIndex: 2,
  color: theme.palette.primary.main,
  marginRight: theme.spacing(-1),
  width: theme.spacing(3),
  height: theme.spacing(3),
  ...(isRead && {
    pointerEvents: 'none',
    opacity: 0
  })
}));

const ItemViewStyled = styled(ItemView, {
  shouldForwardProp: prop => {
    return prop !== 'isRead';
  }
})<{ isRead?: boolean }>(({ isRead, theme }) => ({
  cursor: 'pointer',
  backgroundColor: !isRead && alpha(theme.palette.primary.light, 0.2),
  '&:hover': {
    '.itemNotificationAction button': {
      opacity: 1
    }
  }
}));

const FromNowStyled = styled('div')(({ theme }) => ({
  fontSize: 13,
  lineHeight: 1.2
}));

const ClickArea = styled(Link)(({ theme }) => ({
  height: '100%',
  width: '100%',
  zIndex: 1,
  position: 'absolute'
}));

const TextNotification = styled(ItemSummary)(({ theme }) => ({
  '& a': {
    color: 'unset'
  }
}));

export default function NotificationItemMainCard({
  item,
  user,
  state,
  identity,
  handleAction,
  wrapProps,
  wrapAs,
  actions
}: NotificationItemProps) {
  const { ItemActionMenu } = useGlobal();

  if (!item) return null;

  const { message, creation_date, is_read, link } = item;

  const handleClick = event => {
    if (event.metaKey || event.ctrlKey) {
      return;
    }

    event.preventDefault();
    actions.markAsRead();
    actions.viewItem();
  };

  const markReadNotificationItem = e => {
    actions.markAsRead();
    e.stopPropagation();
  };

  return (
    <ItemViewStyled
      wrapAs={wrapAs}
      isRead={is_read}
      wrapProps={wrapProps}
      {...wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ClickArea to={link} onClick={handleClick} />
      <ItemMedia>
        <UserAvatar user={user as any} size={48} />
      </ItemMedia>
      <ItemText>
        <TextNotification>
          <HtmlViewer html={message} />
        </TextNotification>
        <FromNowStyled>
          <FromNow value={creation_date} />
        </FromNowStyled>
        <ItemAction
          className={'itemNotificationAction'}
          sx={{
            position: 'absolute',
            top: '50%',
            right: 0,
            zIndex: 2,
            transform: 'translateY(-50%)',
            '& button': {
              boxShadow: theme => theme.shadows[1],
              '&[aria-expanded="false"]': {
                opacity: 0
              }
            }
          }}
        >
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            handleAction={handleAction}
            variantPopper="hidden-outview"
          />
        </ItemAction>
      </ItemText>
      <ActionButtonStyled
        onClick={markReadNotificationItem}
        aria-label="markRead"
        size="small"
        isRead={is_read}
      >
        <LineIcon icon={'ico-circle'} sx={{ fontSize: '12px' }} />
      </ActionButtonStyled>
    </ItemViewStyled>
  );
}
