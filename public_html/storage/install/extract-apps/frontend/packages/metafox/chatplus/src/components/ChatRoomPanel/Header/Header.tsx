import { MAX_LENGTH_NAME_GROUP } from '@metafox/chatplus/constants';
import {
  useItemActionRoomDockChat,
  useItemActionRoomPageAll,
  useSessionUser
} from '@metafox/chatplus/hooks';
import {
  BuddyItemShape,
  PublicSettingsShape,
  RoomItemShape,
  RoomType,
  SubscriptionItemShape,
  UserShape
} from '@metafox/chatplus/types';
import { useGlobal, Link } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { LoadingButton } from '@mui/lab';
import { Button, InputBase, styled, Tooltip } from '@mui/material';
import { camelCase, get, isArray, isEmpty, isEqual } from 'lodash';
import React from 'react';
import Avatar from '../../Avatar';
import { PanelToolbar } from '../../DockPanel';
import SearchMessages from './SearchMessages';
import SearchFilter from './SearchFilter';
import FilterMessagesPopper from './FilterMessagesPopper';

const name = 'HeaderRoom';

const Header = styled('div', {
  name,
  slot: 'root',
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
    borderBottom: theme.mixins.border('divider'),
    ...(isMobile &&
      !openSearch && {
        padding: theme.spacing(1, 0.5, 1, 1)
      })
  })
);
const HeaderToolbar = styled('div', {
  name,
  slot: 'Toolbar',
  shouldForwardProp: props => props !== 'openSearch'
})<{ openSearch?: boolean }>(({ theme, openSearch }) => ({
  [theme.breakpoints.down('lg')]: {
    ...(openSearch && {
      width: '100%'
    })
  },
  '& button': {
    [theme.breakpoints.up('sm')]: {
      minWidth: 'auto'
    }
  }
}));
const WrapperTitle = styled('div', {
  name,
  slot: 'title',
  shouldForwardProp: props => props !== 'openSearch'
})<{ openSearch?: boolean }>(({ theme, openSearch }) => ({
  padding: theme.spacing(1.75, 0),
  display: 'flex',
  alignItems: 'center',
  [theme.breakpoints.down('lg')]: {
    ...(openSearch && { display: 'none' })
  }
}));
const TitleName = styled('span')(({ theme }) => ({
  ...theme.typography.h4
}));

const ButtonSaveStyled = styled(LoadingButton)(({ theme }) => ({
  height: theme.spacing(4.25)
}));
const ButtonCancelStyled = styled(Button)(({ theme }) => ({
  height: theme.spacing(4.25),
  marginLeft: theme.spacing(1)
}));

const WrapperButtonIcon = styled(Button, {
  shouldForwardProp: props => props !== 'isInfo'
})<{ isInfo?: boolean }>(({ theme, isInfo }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  ...(isInfo && { color: theme.palette.primary.main }),
  fontSize: theme.spacing(2.25),
  minWidth: 'auto',
  height: 'auto',
  padding: theme.spacing(1),
  margin: 0
}));

const ButtonBackIcon = styled(Button, {
  shouldForwardProp: props => props !== 'isInfo'
})<{ isInfo?: boolean }>(({ theme, isInfo }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  fontSize: theme.spacing(2.25),
  minWidth: theme.spacing(5),
  ...(isInfo && { color: theme.palette.primary.main }),
  padding: 0,
  margin: 0
}));

const IconChangeName = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.spacing(2.25),
  cursor: 'pointer',
  padding: theme.spacing(1)
}));
const NameGroupInput = styled(InputBase)(({ theme }) => ({
  width: '200px',
  height: theme.spacing(4.375),
  margin: theme.spacing(2),
  borderRadius: theme.spacing(3),
  padding: theme.spacing(2),
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['700']
      : theme.palette.grey['100']
}));
const ActionWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'flex-end'
}));

const WrapperTitleAction = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

const ContainerNameStyled = styled('div')(({ theme }) => ({
  display: '-webkit-box',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  WebkitLineClamp: 1,
  WebkitBoxOrient: 'vertical',
  paddingLeft: theme.spacing(1.5),
  width: 'fit-content'
}));

const LinkStyled = styled(Link, {
  shouldForwardProp: props => props !== 'isDirect'
})<{ isDirect?: string }>(({ theme, isDirect }) => ({
  cursor: 'pointer',
  ...(!isDirect && {
    '&:hover': {
      textDecoration: 'none',
      cursor: 'default'
    }
  })
}));

interface Props {
  buddy?: BuddyItemShape;
  perms?: any;
  userChat?: UserShape;
  room?: RoomItemShape;
  toggleInfo?: () => void;
  messageFilter?: any;
  chatRoom?: any;
  searching?: boolean;
  subscription?: SubscriptionItemShape;
  settings?: PublicSettingsShape;
  isSelfChat?: boolean;
  allowMsgNoOne?: boolean;
  archived?: boolean;
  isReadOnly?: boolean;
  isMetaFoxBlocked?: boolean;
  isBlocked?: boolean;
  openInfo?: boolean;
}

interface State {}

export type TypeActionPinStar = 'pin' | 'star';

const NameGroupHeader = ({ name, room, perms, to: toProp }: any) => {
  let to = toProp;
  const { dispatch, i18n } = useGlobal();

  const [isChangeName, setIsChangeName] = React.useState<boolean>(false);
  const [nameChange, setNameChange] = React.useState<string | null>(name);
  const [loading, setLoading] = React.useState(false);

  React.useEffect(() => {
    setIsChangeName(false);
    setNameChange(name);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [room?.id]);

  React.useEffect(() => {
    if (isChangeName) {
      setNameChange(name);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isChangeName]);

  const handleCancelChangeName = () => {
    setIsChangeName(false);
    setNameChange(null);
  };

  const onChangedName = e => {
    setNameChange(e.target.value);
  };

  const handleSaveName = () => {
    if (!nameChange || isEqual(nameChange, name)) {
      setIsChangeName(false);

      return;
    }

    setLoading(true);
    dispatch({
      type: 'chatplus/room/editSettings',
      payload: { identity: room?.id, value: { roomName: nameChange } },
      meta: {
        onSuccess: handleSuccessSaveName,
        onFailure: handleFailureSaveName
      }
    });
  };

  const handleSuccessSaveName = () => {
    setLoading(false);
    setIsChangeName(false);
  };

  const handleFailureSaveName = () => {
    setLoading(false);
    setIsChangeName(false);
  };

  const handleKeyDown = (evt: any) => {
    if (evt.keyCode === 13) {
      // enter
      handleSaveName();
    }
  };

  if (room?.isBotRoom) {
    to = null;
  }

  if (isChangeName) {
    return (
      <>
        <NameGroupInput
          placeholder={name}
          value={nameChange}
          onChange={onChangedName}
          inputProps={{ maxLength: MAX_LENGTH_NAME_GROUP }}
          onKeyDown={handleKeyDown}
        />
        <ButtonSaveStyled
          loading={loading}
          variant="contained"
          onClick={handleSaveName}
        >
          {i18n.formatMessage({ id: 'save' })}
        </ButtonSaveStyled>
        <ButtonCancelStyled variant="outlined" onClick={handleCancelChangeName}>
          {i18n.formatMessage({ id: 'cancel' })}
        </ButtonCancelStyled>
      </>
    );
  }

  return (
    <>
      <ContainerNameStyled data-testid={camelCase('chatplus container name')}>
        <LinkStyled target="_blank" to={to ? to : null} isDirect={to}>
          <TitleName>{nameChange ? nameChange : name}</TitleName>
        </LinkStyled>
      </ContainerNameStyled>
      {perms['edit-room'] && room?.t !== RoomType.Direct && (
        <Tooltip
          title={i18n.formatMessage({ id: 'change_group_name' })}
          placement="top"
        >
          <IconChangeName
            data-testid={camelCase('chatplus change name')}
            icon="ico-pencilline-o"
            onClick={() => setIsChangeName(true)}
          />
        </Tooltip>
      )}
    </>
  );
};

function HeaderRoom({
  buddy,
  perms,
  userChat,
  room,
  chatRoom,
  toggleInfo,
  messageFilter,
  searching,
  subscription,
  settings,
  isSelfChat,
  allowMsgNoOne,
  isReadOnly,
  isMetaFoxBlocked,
  isBlocked,
  openInfo
}: Props) {
  const { dispatch, useActionControl, useIsMobile, navigate, i18n } =
    useGlobal();
  const isMobile = useIsMobile(true);
  const anchorStarRef = React.useRef<HTMLDivElement>();
  const anchorPinRef = React.useRef<HTMLDivElement>();
  const userSession = useSessionUser();

  const [openSearch, setOpenSearch] = React.useState<boolean>(searching);
  const [activeAction, setActiveAction] =
    React.useState<TypeActionPinStar>(null);

  const [handleAction] = useActionControl<State, unknown>(room?.id, {});

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleAction(type, payload, meta);

    if (type === 'chatplus/room/toggleSearching') {
      dispatch({
        type: 'chatplus/room/searchMessages',
        payload: { roomId: room?.id, text: '' }
      });
      setOpenSearch(prev => !prev);
    }

    if (type === 'chatplus/room/sideInfo') {
      toggleInfo();
    }
  };

  const isMuted =
    userSession &&
    room &&
    room.muted &&
    isArray(room.muted) &&
    !!room.muted.find(x => x === userSession.username);

  const to =
    room?.t === RoomType.Direct && buddy?.username && `/${buddy.username}`;

  const name = React.useMemo(() => {
    let result = i18n.formatMessage({ id: 'name' });

    if (room?.isNameChanged) return buddy?.name || result;

    const nameSplit = buddy?.name.split(',');

    if (buddy?.name && nameSplit.length > 2) {
      result = `${nameSplit[0]}, ${nameSplit[1]} +${nameSplit.length - 2}`;
    } else {
      result = buddy?.name;
    }

    return result;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [buddy?.name, room?.isNameChanged]);

  const itemActionPageAll = useItemActionRoomPageAll();
  const itemActionDockChat = useItemActionRoomDockChat();

  const items = React.useMemo(
    () =>
      filterShowWhen(itemActionPageAll, {
        room,
        subscription,
        groups: chatRoom?.groups,
        pinned: chatRoom?.pinned,
        favorite: subscription?.f,
        starred: chatRoom?.starred,
        searching: chatRoom?.searching,
        settings,
        perms,
        isMobile,
        isSelfChat,
        isBlocked,
        isMetaFoxBlocked,
        allowMsgNoOne,
        isReadOnly,
        isMuted
      }),
    [
      chatRoom,
      subscription,
      room,
      settings,
      perms,
      isMobile,
      itemActionPageAll,
      isSelfChat,
      isBlocked,
      isMetaFoxBlocked,
      allowMsgNoOne,
      isReadOnly,
      isMuted
    ]
  );
  const itemsFilter = React.useMemo(
    () =>
      filterShowWhen(itemActionDockChat, {
        room,
        subscription,
        groups: chatRoom?.groups,
        pinned: chatRoom?.pinned,
        favorite: subscription?.f,
        starred: chatRoom?.starred,
        searching: chatRoom?.searching,
        settings,
        perms,
        isMobile,
        isSelfChat,
        isBlocked,
        isMetaFoxBlocked,
        allowMsgNoOne,
        isReadOnly
      }),
    [
      chatRoom,
      subscription,
      room,
      settings,
      perms,
      isMobile,
      itemActionDockChat,
      isSelfChat,
      isBlocked,
      isMetaFoxBlocked,
      allowMsgNoOne,
      isReadOnly
    ]
  );

  const isShowActionSearch = get(perms, 'search-msg');
  const isShowActionVoiceCall = Boolean(
    itemsFilter.findIndex(i => i.testid === 'startVoiceCall') !== -1
  );
  const isShowActionVideoCall = Boolean(
    itemsFilter.findIndex(i => i.testid === 'startVideoChat') !== -1
  );

  const isShowMoreMenu =
    !isEmpty(items) &&
    items.length > 1 &&
    items.filter(item => item?.behavior !== 'more');

  const status = userChat?.status || 0;

  React.useEffect(() => {
    setOpenSearch(searching);
  }, [searching]);

  React.useEffect(() => {
    setOpenSearch(false);

    return () => {
      setActiveAction(null);
    };
  }, [room?.id]);

  const toggleSearch = React.useCallback(() => {
    dispatch({
      type: 'chatplus/room/toggleSearching',
      payload: { identity: room?.id }
    });

    dispatch({
      type: 'chatplus/room/clearMsgSearch',
      payload: { rid: room?.id }
    });
    setOpenSearch(prev => !prev);
    setActiveAction(null);
  }, [dispatch, room]);

  const handleVoiceCall = React.useCallback(() => {
    dispatch({
      type: 'chatplus/room/startVoiceCall',
      payload: { identity: room?.id }
    });
  }, [dispatch, room]);

  const handleVideoChat = React.useCallback(() => {
    dispatch({
      type: 'chatplus/room/startVideoChat',
      payload: { identity: room?.id }
    });
  }, [dispatch, room]);

  const togglePinnedMessages = React.useCallback(() => {
    if (activeAction === 'pin') {
      setActiveAction(null);

      return;
    }

    setActiveAction('pin');
  }, [activeAction]);

  const toggleStaredMessages = React.useCallback(() => {
    if (activeAction === 'star') {
      setActiveAction(null);

      return;
    }

    setActiveAction('star');
  }, [activeAction]);

  const handleBack = () => {
    navigate({
      pathname: '/messages'
    });
  };

  const handleClosePopupFilter = React.useCallback(() => {
    if (!activeAction) return;

    setActiveAction(null);
  }, [activeAction]);

  const handleToggleInfo = React.useCallback(() => {
    toggleInfo();
    setActiveAction(null);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const anchorRef = React.useMemo(() => {
    if (activeAction === 'pin') {
      return anchorPinRef;
    }

    if (activeAction === 'star') {
      return anchorStarRef;
    }

    return undefined;
  }, [activeAction]);

  if (isMobile) {
    return (
      <Header isMobile={isMobile} openSearch={openSearch}>
        {openSearch ? null : (
          <ButtonBackIcon onClick={handleBack}>
            <LineIcon icon="ico-arrow-left" />
          </ButtonBackIcon>
        )}
        {openSearch ? (
          chatRoom?.pinned || chatRoom?.starred ? (
            <SearchFilter
              hide={!searching}
              roomId={room?.id}
              searching={searching}
              chatRoom={chatRoom}
              isBuddy
            />
          ) : (
            <SearchMessages
              toggleSearch={toggleSearch}
              room={room}
              messageFilter={messageFilter}
              searching={searching}
            />
          )
        ) : (
          <WrapperTitleAction>
            <WrapperTitle>
              <Avatar
                name={name}
                username={buddy?.username}
                size={48}
                src={buddy?.avatar}
                room={room}
                status={status}
                avatarETag={buddy?.avatarETag}
              />
              <ContainerNameStyled>
                <TitleName>{name}</TitleName>
              </ContainerNameStyled>
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
    <Header data-testid={camelCase('block chatroom header')}>
      <WrapperTitle openSearch={openSearch}>
        <Avatar
          target="_blank"
          to={to}
          noLink={room?.isBotRoom}
          name={name}
          username={buddy?.username}
          size={48}
          src={buddy?.avatar}
          room={room}
          status={status}
          avatarETag={buddy?.avatarETag}
        />
        <NameGroupHeader name={name} room={room} perms={perms} to={to} />
      </WrapperTitle>
      <HeaderToolbar
        data-testid={camelCase('block header toolbar')}
        openSearch={openSearch}
      >
        {openSearch ? (
          <SearchMessages
            toggleSearch={toggleSearch}
            room={room}
            messageFilter={messageFilter}
            searching={searching}
          />
        ) : (
          <ActionWrapper>
            {isShowActionSearch && (
              <Tooltip
                title={i18n.formatMessage({ id: 'search' }) ?? ''}
                placement="top"
              >
                <WrapperButtonIcon
                  data-testid={camelCase('header action search')}
                  onClick={toggleSearch}
                >
                  <LineIcon icon="ico-search-o" />
                </WrapperButtonIcon>
              </Tooltip>
            )}
            <Tooltip
              title={i18n.formatMessage({ id: 'pinned_messages' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                data-testid={camelCase('header action pin')}
                ref={anchorPinRef}
                onClick={togglePinnedMessages}
                isInfo={activeAction === 'pin'}
              >
                <LineIcon icon="ico-thumb-tack" />
              </WrapperButtonIcon>
            </Tooltip>
            <Tooltip
              title={i18n.formatMessage({ id: 'starred_messages' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                data-testid={camelCase('header action starred')}
                ref={anchorStarRef}
                onClick={toggleStaredMessages}
                isInfo={activeAction === 'star'}
              >
                <LineIcon icon="ico-star-o" />
              </WrapperButtonIcon>
            </Tooltip>
            <FilterMessagesPopper
              open={activeAction === 'pin' || activeAction === 'star'}
              anchorRef={anchorRef}
              type={activeAction}
              closePopover={handleClosePopupFilter}
            />
            <Tooltip
              title={i18n.formatMessage({ id: 'conversation_info' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                data-testid={camelCase('header action info')}
                onClick={handleToggleInfo}
                isInfo={openInfo}
              >
                <LineIcon icon="ico-info-circle-alt-o" />
              </WrapperButtonIcon>
            </Tooltip>
            {!isSelfChat && isShowActionVoiceCall && (
              <Tooltip
                title={i18n.formatMessage({ id: 'start_an_audio_call' }) ?? ''}
                placement="top"
              >
                <WrapperButtonIcon
                  data-testid={camelCase('header action call')}
                  onClick={handleVoiceCall}
                >
                  <LineIcon icon="ico-phone-o" />
                </WrapperButtonIcon>
              </Tooltip>
            )}
            {!isSelfChat && isShowActionVideoCall && (
              <Tooltip
                title={i18n.formatMessage({ id: 'start_a_video_call' }) ?? ''}
                placement="top"
              >
                <WrapperButtonIcon
                  data-testid={camelCase('header action video call')}
                  onClick={handleVideoChat}
                >
                  <LineIcon icon="ico-videocam-o" />
                </WrapperButtonIcon>
              </Tooltip>
            )}
            {isShowMoreMenu ? (
              <WrapperButtonIcon sx={{ p: 0, height: '34px', width: '34px' }}>
                <PanelToolbar
                  items={items}
                  handleAction={handleAction}
                  variant="pageMessage"
                />
              </WrapperButtonIcon>
            ) : null}
          </ActionWrapper>
        )}
      </HeaderToolbar>
    </Header>
  );
}

export default HeaderRoom;
