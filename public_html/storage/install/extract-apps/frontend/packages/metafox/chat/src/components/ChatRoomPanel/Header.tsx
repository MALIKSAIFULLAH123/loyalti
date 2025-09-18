import { PanelToolbar } from '@metafox/chat/components/DockPanel';
import { useItemActionRoomPageAll } from '@metafox/chat/hooks';
import { Link, useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText, UserAvatar } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Button, styled, Tooltip } from '@mui/material';
import React from 'react';
import SearchMessages from './SearchMessages';
import { camelCase } from 'lodash';

const Header = styled('div', {
  shouldForwardProp: props => props !== 'isMobile' && props !== 'openSearch'
})<{ isMobile?: boolean; openSearch?: boolean }>(
  ({ theme, isMobile, openSearch }) => ({
    backgroundColor: theme.palette.background.paper,
    alignItems: 'center',
    boxSizing: 'border-box',
    display: 'flex',
    height: theme.spacing(9),
    padding: theme.spacing(1, 1, 1, 2),
    justifyContent: 'space-between',
    borderBottom: theme.mixins.border('secondary'),
    ...(isMobile &&
      !openSearch && {
        padding: theme.spacing(1, 0.5, 1, 1)
      })
  })
);
const HeaderToolbar = styled('div')(({ theme }) => ({}));
const WrapperTitle = styled(Link)(({ theme }) => ({
  padding: theme.spacing(1.75, 0),
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  '&:hover': {
    textDecoration: 'none'
  }
}));
const TitleName = styled(TruncateText)(({ theme }) => ({
  margin: theme.spacing(0, 1.5, 0, 1.5)
}));

const ButtonBackIcon = styled(Button, {
  shouldForwardProp: props => props !== 'isInfo'
})<{ isInfo?: boolean }>(({ theme, isInfo }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(6.25),
  ...(isInfo && { color: theme.palette.primary.main }),
  padding: 0,
  margin: 0
}));

const ActionWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const WrapperTitleAction = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

const WrapperButtonIcon = styled(Button, {
  shouldForwardProp: props => props !== 'isInfo'
})<{ isInfo?: boolean }>(({ theme, isInfo }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(4),
  ...(isInfo && { color: theme.palette.primary.main })
}));

interface Props {
  room?: any;
  searching?: boolean;
}

interface State {}

function HeaderRoom({ room, searching }: Props) {
  const {
    dispatch,
    useActionControl,
    useIsMobile,
    navigate,
    useGetItem,
    i18n
  } = useGlobal();
  const isMobile = useIsMobile(true);

  const [openSearch, setOpenSearch] = React.useState<boolean>(false);

  const userIdentity = room?.other_members[0] || undefined;

  const user = useGetItem(userIdentity);

  const [handleAction] = useActionControl<State, unknown>(room?.id, {});

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleAction(type, payload, meta);

    if (type === 'chat/room/toggleSearching') {
      dispatch({
        type: 'chat/room/searchMessages',
        payload: { roomId: room?.id, text: '' }
      });
      setOpenSearch(prev => !prev);
    }
  };

  const itemActionPageAll = useItemActionRoomPageAll();

  const items = React.useMemo(
    () =>
      filterShowWhen(itemActionPageAll, {
        isMobile,
        item: room
      }),
    [itemActionPageAll, isMobile, room]
  );

  React.useEffect(() => {
    setOpenSearch(false);
  }, [room]);

  const toggleSearch = React.useCallback(() => {
    dispatch({
      type: 'chat/room/toggleSearching',
      payload: { identity: room?.id }
    });

    dispatch({
      type: 'chat/room/searchMessages',
      payload: { roomId: room?.id, text: '' }
    });
    setOpenSearch(prev => !prev);

    dispatch({
      type: 'chat/room/clearMsgSearch',
      payload: { rid: room?.id }
    });
  }, [dispatch, room]);

  const handleBack = () => {
    navigate({
      pathname: '/messages'
    });
  };

  if (isMobile) {
    return (
      <Header isMobile={isMobile} openSearch={openSearch}>
        {openSearch ? null : (
          <ButtonBackIcon onClick={handleBack}>
            <LineIcon icon="ico-arrow-left" />
          </ButtonBackIcon>
        )}
        {openSearch ? (
          <SearchMessages
            toggleSearch={toggleSearch}
            room={room}
            searching={searching}
          />
        ) : (
          <WrapperTitleAction>
            <WrapperTitle to={user?.link}>
              <UserAvatar user={user} size={48} noLink />
              <TitleName lines={2} variant="h4">
                {room?.name}
              </TitleName>
            </WrapperTitle>
            <HeaderToolbar>
              <ActionWrapper>
                <PanelToolbar
                  items={items}
                  handleAction={handleActionLocalFunc}
                  displayLimit={2}
                />
              </ActionWrapper>
            </HeaderToolbar>
          </WrapperTitleAction>
        )}
      </Header>
    );
  }

  return (
    <Header data-testid={camelCase('_chat Header room')}>
      <WrapperTitle
        data-testid={camelCase('_chat Header title')}
        to={user?.link}
      >
        {room ? <UserAvatar user={user} size={48} noLink /> : null}
        <TitleName lines={2} variant="h4">
          {room?.name}
        </TitleName>
      </WrapperTitle>
      <HeaderToolbar data-testid={camelCase('_chat Header room toolbar')}>
        {openSearch ? (
          <SearchMessages
            toggleSearch={toggleSearch}
            room={room}
            searching={searching}
          />
        ) : (
          <ActionWrapper data-testid={camelCase('_chat room wrapper action')}>
            <Tooltip
              title={i18n.formatMessage({ id: 'search' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                data-testid={camelCase('_chat room action search')}
                onClick={toggleSearch}
              >
                <LineIcon icon="ico-search-o" />
              </WrapperButtonIcon>
            </Tooltip>
            <PanelToolbar
              items={items}
              handleAction={handleAction}
              variant="pageMessage"
            />
          </ActionWrapper>
        )}
      </HeaderToolbar>
    </Header>
  );
}

export default HeaderRoom;
