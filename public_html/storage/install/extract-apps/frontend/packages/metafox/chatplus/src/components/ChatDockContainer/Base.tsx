import UnreadMessageBadgeAware from '../UnreadMessageBadgeAware';
import { NEW_CHAT_ROOM } from '@metafox/chatplus/constants';
import {
  useOpenChatRooms,
  usePublicSettings,
  useSessionUser
} from '@metafox/chatplus/hooks';
import { ChatplusConfig } from '@metafox/chatplus/types';
import {
  IS_ADMINCP,
  LAYOUT_EDITOR_TOGGLE,
  useGlobal,
  useIsMobile
} from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import { useLocation } from 'react-router-dom';
import ListenIncomingCall from '@metafox/chatplus/components/CallApp/IncomingCall/ListenIncomingCall';
import FlyBuddyMinimizeSide from '@metafox/chatplus/components/FlyBuddyMinimizeSide';
import FlyNewChatRoomPanel from '@metafox/chatplus/components/FlyNewChatRoomPanel';
import UserChangeStatusAware from '../UserChangeStatusAware';

const name = 'ChatDockContainer';

const Root = styled('div', { name, slot: 'root' })(({ theme }) => ({}));
const DockContainer = styled('div', { name, slot: 'dock-container' })(
  ({ theme }) => ({
    position: 'fixed',
    zIndex: 998,
    right: 0,
    bottom: 0,
    transform: 'translateZ(0)',
    display: 'flex',
    flexDirection: 'row-reverse'
  })
);

const PanelSide = styled('div', { name, slot: 'side' })(({ theme }) => ({
  position: 'absolute',
  bottom: 24,
  right: 24,
  zIndex: 1000
}));

const PanelContainer = styled('div', { name, slot: 'room-container' })(
  ({ theme }) => ({
    position: 'absolute',
    bottom: 0,
    right: 90,
    display: 'flex',
    flexDirection: 'row-reverse',
    '&:after': {
      clear: 'both',
      content: "'.'",
      display: 'block',
      fontSize: '0',
      height: '0',
      lineHeight: 0,
      visibility: 'hidden'
    }
  })
);

export default function ChatDockContainer() {
  const { getSetting, chatplus, localStore, jsxBackend } = useGlobal();
  const location = useLocation() as any;

  const user = useSessionUser();
  const openRooms = useOpenChatRooms();
  const settings = usePublicSettings();
  const isMobile = useIsMobile(true);
  const setting = getSetting<ChatplusConfig>('chatplus');

  const { Website, Metafox_Notification_Sound_Url } = settings;
  const audioNotificationSrc = `${Website}${Metafox_Notification_Sound_Url}`;

  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);

  const isNoShow = React.useMemo(() => {
    function funcCheckShow() {
      return ['messages', 'page-not-found', 'chatplus/call'].some(item => {
        return location.pathname.includes(item);
      });
    }

    let result = false;

    result = funcCheckShow();

    // homepage
    if (isEditMode || isMobile || IS_ADMINCP) result = true;

    return result;
  }, [isMobile, location.pathname, isEditMode]);

  const isNoShowPopupCall = React.useMemo(() => {
    function funcCheckShow() {
      return ['chatplus/call'].some(item => {
        return location.pathname.includes(item);
      });
    }

    const result = funcCheckShow() || false;

    return result;
  }, [location.pathname]);

  const dataFlyChatRoomPanel = React.useMemo(
    () => openRooms?.values?.filter(item => !item?.collapsed),
    [openRooms?.values]
  );

  React.useEffect(() => {
    if (openRooms.init) {
      chatplus.saveChatRooms(openRooms?.values);
    }
  }, [openRooms, chatplus]);

  if (!setting || !setting.server) return null;

  if (!user) return null;

  return (
    <div>
      {isNoShow ? null : (
        <Root>
          <DockContainer>
            <PanelSide>
              <FlyBuddyMinimizeSide />
            </PanelSide>
            <PanelContainer>
              {openRooms.newChatRoom ? (
                <FlyNewChatRoomPanel
                  rid={NEW_CHAT_ROOM}
                  active={openRooms.active === NEW_CHAT_ROOM}
                />
              ) : null}
              {!openRooms.closeIconMsg &&
                dataFlyChatRoomPanel.map(room => {
                  return jsxBackend.render({
                    component: 'chatplus.ui.flyChatRoomPanel',
                    props: {
                      active: openRooms.active === room.rid,
                      collapsed: room.collapsed,
                      rid: room.rid,
                      textDefault: room.text || '',
                      key: room.rid
                    }
                  });
                })}
            </PanelContainer>
          </DockContainer>
        </Root>
      )}
      {isNoShowPopupCall ? null : <ListenIncomingCall />}
      <audio
        style={{ display: 'none' }}
        id="chatplusSoundNotification"
        src={audioNotificationSrc}
        muted
      ></audio>
      <UnreadMessageBadgeAware />
      <UserChangeStatusAware />
    </div>
  );
}
