import {
  ChatMsgPassProps,
  MsgGroupShape,
  RoomItemShape
} from '@metafox/chat/types';
import React from 'react';
import MsgSet from './MsgSet';

interface MsgGroupProps extends ChatMsgPassProps {
  msgGroup: MsgGroupShape;
  showToolbar?: boolean;
  room?: RoomItemShape;
}

export default function MsgGroup({
  msgGroup,
  disableReact,
  handleAction,
  showToolbar,
  room
}: MsgGroupProps) {
  if (!msgGroup) return null;

  const { items } = msgGroup;

  return items ? (
    <div>
      {items.map((msgSet, i) => (
        <MsgSet
          msgSet={msgSet}
          key={`k.0${i}`}
          disableReact={disableReact}
          showToolbar={showToolbar}
          handleAction={handleAction}
          room={room}
        />
      ))}
    </div>
  ) : null;
}
