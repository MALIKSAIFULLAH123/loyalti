/**
 * @type: ui
 * name: chatplus.messageContent.messageEmpty
 * chunkName: chatplusUI
 */

import { MsgContentProps } from '@metafox/chatplus/types';
import React from 'react';
import MsgAttachments from '../MsgAttachments';
import MsgEmbeds from '../MsgEmbeds';

export default function MessageEmpty({ message }: MsgContentProps) {
  return (
    <div className="uiChatMsgItemBodyInnerWrapper">
      <MsgAttachments message={message} />
      <MsgEmbeds message={message} />
    </div>
  );
}
