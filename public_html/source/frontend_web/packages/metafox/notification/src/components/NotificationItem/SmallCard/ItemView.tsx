import HtmlViewer from '@metafox/html-viewer';
import { NotificationItemProps } from '@metafox/notification/types';
import {
  FromNow,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemView,
  LineIcon,
  UserAvatar,
  ItemAction
} from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';
import { alpha } from '@mui/material/styles';
import { useGlobal, Link } from '@metafox/framework';

const ActionButtonStyled = styled(IconButton, {
  shouldForwardProp: prop => {
    return prop !== 'isRead';
  }
})<{ isRead?: boolean }>(({ theme, isRead }) => ({
  position: 'relative',
  zIndex: 3,
  color: theme.palette.primary.main,
  marginRight: theme.spacing(-1),
  width: theme.spacing(3),
  height: theme.spacing(3),
  ...(isRead && {
    pointerEvents: 'none',
    opacity: 0
  })
}));

const FromNowStyled = styled('div')(({ theme }) => ({
  fontSize: 13,
  lineHeight: 1.2
}));

const ItemViewStyled = styled(ItemView, {
  shouldForwardProp: prop => {
    return prop !== 'isRead';
  }
})<{ isRead?: boolean }>(({ isRead, theme }) => ({
  position: 'relative',
  cursor: 'pointer',
  overflow: 'visible',
  backgroundColor: isRead
    ? 'transparent'
    : alpha(theme.palette.primary.light, 0.1),
  '&:hover': {
    '.itemNotificationAction button': {
      opacity: 1
    }
  },
  '& a': {
    fontWeight: theme.typography.fontWeightBold,
    color: `${theme.palette.text.primary} !important`
  }
}));

const TextNotification = styled(ItemSummary)(({ theme }) => ({
  '& a': {
    color: 'unset'
  }
}));

const StyledLink = styled(Link)(({ theme }) => ({
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  zIndex: 1,
  position: 'absolute'
}));

export default function NotificationItemSmallCard({
  item,
  identity,
  user,
  actions,
  wrapProps,
  wrapAs,
  handleAction
}: NotificationItemProps) {
  const { usePopover, ItemActionMenu } = useGlobal();
  const { closePopover } = usePopover() || {};

  if (!item) return null;

  const { message, creation_date, is_read, link } = item;

  const handleClick = e => {
    if (e.metaKey || e.ctrlKey) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    actions.markAsRead();

    actions.viewItem();

    closePopover();
  };

  const markReadNotificationItem = e => {
    actions.markAsRead();
    e.stopPropagation();
  };

  return (
    <ItemViewStyled
      {...wrapProps}
      wrapAs={wrapAs}
      isRead={is_read}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <StyledLink to={link} onClick={handleClick} />
      <ItemMedia>
        <UserAvatar
          user={user as any}
          size={48}
          noStory
          showStatus={false}
          noLink
        />
      </ItemMedia>
      <ItemText>
        <TextNotification lines={3}>
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
            transform: 'translateY(-50%)',
            zIndex: 2,
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
            menuName="itemActionMenu"
            handleAction={handleAction}
            size="smaller"
            variant="white-contained"
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
