/**
 * @type: ui
 * name: messages.ui.MessagesPopper
 * chunkName: boot
 */

import { NEW_CHAT_ROOM } from '@metafox/chatplus/constants';
import { useGetNotifications } from '@metafox/chatplus/hooks';
import { ChatplusConfig } from '@metafox/chatplus/types';
import { RefOf, useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon, Popper, SearchBox } from '@metafox/ui';
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

const ActionItem = styled('div')(({ theme }) => ({ cursor: 'pointer' }));
const WrapperButtonIcon = styled(Button)(({ theme }) => ({
  color:
    theme.palette.mode === 'dark'
      ? theme.palette.text.primary
      : theme.palette.grey['700'],
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(0),
  borderRadius: '50%',
  height: '34px',
  '&:hover': {
    backgroundColor: theme.palette.action.selected
  }
}));

const ButtonIcon = styled(Button)(({ theme }) => ({
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(6.25),
  color: theme.palette.text.primary,
  '& .ico.ico-check-circle-alt': {
    marginRight: theme.spacing(0.75),
    fontSize: theme.spacing(1.75)
  }
}));

const TotalUnread = styled(Typography)(({ theme }) => ({
  marginLeft: theme.spacing(0.5)
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
  padding: theme.spacing(0, 2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  color: theme.palette.text.primary
}));
const NoContent = styled('div')(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  margin: theme.spacing(1, 2, 2, 2),
  fontSize: theme.spacing(2),
  color: theme.palette.text.secondary
}));

const WrapperSearch = styled('div')(({ theme }) => ({
  padding: theme.spacing(1, 1, 1, 2),
  display: 'flex',
  alignItems: 'center'
}));

const SearchWrapper = styled(SearchBox, {
  name: 'SearchWrapper'
})(({ theme }) => ({
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
  const { i18n, dispatch, getSetting, navigate, usePageParams, jsxBackend } =
    useGlobal();
  const params = usePageParams();
  const [openSearch, setOpenSearch] = React.useState(false);
  const [searchValue, setSearchValue] = React.useState<string>('');
  const [isConversation, setIsConversation] = React.useState(null);

  const notifications = useGetNotifications();

  React.useEffect(() => {
    dispatch({
      type: 'chatplus/room/getFirstRoom',
      meta: {
        onSuccess: value => setIsConversation(value)
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleResetSearch = () => {
    if (open) {
      setSearchValue('');
      setOpenSearch(false);
      dispatch({
        type: 'chatplus/spotlight/reset'
      });
    }
  };

  React.useEffect(() => {
    handleResetSearch();

    if (open) {
      dispatch({
        type: 'chatplus/status/clearAlert'
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  const setting = getSetting<ChatplusConfig>('chatplus');

  const totalUnread =
    notifications?.unread > 99 ? '99+' : notifications?.unread;

  const handleClickNewGroup = () => {
    dispatch({
      type: 'chatplus/room/newGroup',
      payload: {}
    });
  };

  const handleClickNewConversation = React.useCallback(() => {
    if (params?.isAllPageMessages) {
      dispatch({
        type: 'chatplus/room/newConversationPage',
        payload: {}
      });
    } else {
      dispatch({
        type: 'chatplus/openRooms/addRoomToChatDock',
        payload: { rid: NEW_CHAT_ROOM }
      });
    }

    closePopover && closePopover();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleClickMarkAllRead = React.useCallback(() => {
    dispatch({
      type: 'chatplus/room/markAllRead'
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleClickViewAll = React.useCallback(() => {
    navigate(
      isConversation?.id ? `/messages/${isConversation.id}` : '/messages'
    );

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onSearchInputChanged = evt => {
    if (evt && evt.target) {
      setSearchValue(evt.target.value);
    }
  };

  if (!setting || !setting.server) {
    return (
      <Popper
        id="chatplus-popover"
        data-testid="chatplus-popover"
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
          <Header noContent>
            <TitleHeader>
              <Typography variant="h4">
                {i18n.formatMessage({ id: 'messages' })}
              </Typography>
            </TitleHeader>
          </Header>
          <NoContent data-testid="noResultFound">
            {i18n.formatMessage({ id: 'no_content' })}
          </NoContent>
        </Paper>
      </Popper>
    );
  }

  if (!open) return null;

  return (
    <Popper
      id="chatplus-popover"
      data-testid="chatplus-popover"
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
            <TotalUnread variant="body1">
              ({totalUnread} {i18n.formatMessage({ id: 'unread' })})
            </TotalUnread>
          </TitleHeader>
          <ActionItem>
            <Tooltip
              title={i18n.formatMessage({ id: 'create_new_group' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon onClick={handleClickNewGroup}>
                <LineIcon icon="ico-user2-three-o" />
              </WrapperButtonIcon>
            </Tooltip>
            <Tooltip
              title={i18n.formatMessage({ id: 'new_conversation' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon onClick={handleClickNewConversation}>
                <LineIcon icon="ico-compose" />
              </WrapperButtonIcon>
            </Tooltip>
          </ActionItem>
        </Header>
        <WrapperSearch>
          {openSearch ? (
            <WrapperButtonIcon onClick={handleResetSearch}>
              <LineIcon icon="ico-arrow-left" />
            </WrapperButtonIcon>
          ) : null}
          <SearchWrapper
            placeholder={i18n.formatMessage({ id: 'search_people_group' })}
            value={searchValue}
            onChange={onSearchInputChanged}
            onFocus={() => setOpenSearch(true)}
          />
        </WrapperSearch>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={320}
          autoHeightMin={40}
        >
          {jsxBackend.render({
            component: 'chatplus.ui.buddyList',
            props: {
              searchValue,
              closePopover,
              handleResetSearch
            }
          })}
        </ScrollContainer>
        <Footer>
          <ActionItem>
            <ButtonIcon onClick={handleClickMarkAllRead}>
              <LineIcon icon="ico-check-circle-alt" />
              <Typography variant="body1">
                {i18n.formatMessage({ id: 'mark_all_as_read' })}
              </Typography>
            </ButtonIcon>
          </ActionItem>
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
