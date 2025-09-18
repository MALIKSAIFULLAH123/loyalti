import { ChatMsgPassProps, MsgGroupShape } from '@metafox/chatplus/types';
import { isNotSeenMsg } from '@metafox/chatplus/utils';
import React from 'react';
import MsgSet from './MsgSet';

interface MsgGroupProps extends ChatMsgPassProps {
  msgGroup: MsgGroupShape;
  showToolbar?: any;
}

const findMsgSetShowSeenUser = (msgs: any): number => {
  let x = msgs.length;

  if (!x) return -1;

  while (x--) {
    const isAlert = isNotSeenMsg(msgs[x].t);

    if (!isAlert) return x;
  }

  return -1;
};

export default function MsgGroup({
  msgGroup,
  archived,
  settings,
  tooltipPosition,
  isMobile,
  disableReact,
  room,
  subscription,
  user,
  isReadonly,
  isRoomOwner,
  isRoomLimited,
  handleAction,
  showToolbar = true
}: MsgGroupProps) {
  if (!msgGroup) return null;

  const { items } = msgGroup;
  const indexMsgSeenUser = findMsgSetShowSeenUser(items);

  return items ? (
    <div>
      {items.map((msgSet, i) => (
        <MsgSet
          user={user}
          msgSet={msgSet}
          key={`k.0${i}`}
          archived={archived}
          settings={settings}
          tooltipPosition={tooltipPosition}
          isMobile={isMobile}
          disableReact={disableReact}
          room={room}
          subscription={subscription}
          seenUserShow={indexMsgSeenUser === i}
          isReadonly={isReadonly}
          isRoomLimited={isRoomLimited}
          isRoomOwner={isRoomOwner}
          handleAction={handleAction}
          showToolbar={showToolbar}
        />
      ))}
    </div>
  ) : null;
}
