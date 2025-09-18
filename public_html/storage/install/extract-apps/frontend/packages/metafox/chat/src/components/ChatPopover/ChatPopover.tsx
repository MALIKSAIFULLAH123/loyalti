/**
 * @type: ui
 * name: messages.ui.MessagesPopper
 */

import { NEW_CHAT_ROOM } from '@metafox/chat/constants';
import { useGetNotifications } from '@metafox/chat/hooks';
import { RefOf, useGlobal } from '@metafox/framework';
import { ScrollContainer, SearchBox } from '@metafox/layout';
import { LineIcon, Popper } from '@metafox/ui';
import {
  Box,
  Button,
  Paper,
  PopperProps,
  styled,
  Tooltip,
  Typography
} from '@mui/material';
import React from 'react';

const ActionItem = styled('div')(({ theme }) => ({
  cursor: 'pointer',
  '&:hover': {
    textDecoration: 'underline'
  }
}));
const WrapperButtonIcon = styled(Button)(({ theme }) => ({
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.grey['700'],
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(0)
}));

const TitleHeader = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'flex-end'
}));
const Header = styled(Box, {
  shouldForwardProp: props => props !== 'noContent'
})<{ noContent?: boolean }>(({ theme, noContent }) => ({
  padding: theme.spacing(1.5, 2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',

  ...(!noContent && {
    borderBottom: '1px solid',
    borderColor: theme.palette.border?.secondary
  })
}));
const Footer = styled(Box)(({ theme }) => ({
  padding: theme.spacing(1, 2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: theme.palette.text.primary
}));

const WrapperSearch = styled('div')(({ theme }) => ({
  padding: theme.spacing(1, 1, 1, 2),
  display: 'flex',
  alignItems: 'center',
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

export default function MessagesPopper({
  anchorRef,
  open,
  closePopover,
  ...rest
}: PopperProps & { anchorRef: RefOf<HTMLDivElement>; closePopover: () => {} }) {
  const { i18n, dispatch, ListView } = useGlobal();
  const [query, setQuery] = React.useState<string>('');

  const notifications = useGetNotifications();

  const pagingId = 'pagination.messagesPopper.listRooms';
  const dataSource = React.useMemo(
    () => ({
      apiUrl: '/chat-room',
      apiParams: { limit: 10 }
    }),
    []
  );

  React.useEffect(() => {
    setQuery('');

    if (notifications?.total_notification > 0 && open) {
      dispatch({
        type: 'chat/notifications/clearUnread'
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, notifications]);

  const handleClickNewChat = React.useCallback(() => {
    dispatch({
      type: 'chat/openRoomPanel',
      payload: { rid: NEW_CHAT_ROOM }
    });

    closePopover && closePopover();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleClickViewAll = React.useCallback(() => {
    dispatch({
      type: 'chat/navigateMessagesPage'
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onQueryChange = value => {
    setQuery(value);
  };

  return (
    <Popper
      id="chat-popover"
      data-testid="chat-popover"
      anchorEl={anchorRef.current}
      open={open}
      popperOptions={{
        strategy: 'fixed'
      }}
      {...rest}
    >
      <Paper
        sx={{
          width: 360,
          overflow: 'hidden',
          userSelect: 'none'
        }}
      >
        <Header>
          <TitleHeader>
            <Typography variant="h4">
              {i18n.formatMessage({ id: 'messages' })}
            </Typography>
          </TitleHeader>
          <ActionItem>
            <WrapperButtonIcon onClick={handleClickNewChat}>
              <Tooltip
                title={i18n.formatMessage({ id: 'new_conversation' }) ?? ''}
                placement="top"
              >
                <LineIcon icon="ico-compose" />
              </Tooltip>
            </WrapperButtonIcon>
          </ActionItem>
        </Header>
        <WrapperSearch>
          <SearchBox
            placeholder={i18n.formatMessage({ id: 'search_people' })}
            value={query}
            onQueryChange={onQueryChange}
            sx={{ width: '100%' }}
          />
        </WrapperSearch>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={320}
          autoHeightMin={40}
        >
          <ListView
            acceptQuerySearch
            query={query}
            dataSource={dataSource}
            pagingId={pagingId}
            canLoadMore
            clearDataOnUnMount
            gridLayout="ChatRoom - Main Card - Popper"
            itemLayout="ChatRoom - Main Card - Popper"
            itemView="blocked.itemView.chatroomCard"
            blockLayout="Chat buddy list popper"
            emptyPage="core.block.no_content"
            emptyPageProps={{
              title: 'no_messages',
              contentStyle: {
                sx: {
                  marginTop: '12px'
                }
              }
            }}
          />
        </ScrollContainer>
        <Footer>
          <ActionItem>
            <Typography variant="body1" onClick={handleClickViewAll}>
              {i18n.formatMessage({ id: 'view_all_messages' })}
            </Typography>
          </ActionItem>
        </Footer>
      </Paper>
    </Popper>
  );
}
