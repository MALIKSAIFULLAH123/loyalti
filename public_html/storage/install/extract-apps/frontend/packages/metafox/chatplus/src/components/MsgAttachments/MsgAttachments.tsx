import { MsgItemShape } from '@metafox/chatplus/types';
import React from 'react';
import MsgAttachment from './MsgAttachment';
import MsgAttachmentMultiMedia from './MsgAttachmentMultiMedia';

export interface Props {
  message: MsgItemShape;
  isOwner?: boolean;
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  dataQuote: any;
  isQuote: boolean;
}

export default function MsgAttachments({
  message,
  isOwner,
  msgType,
  dataQuote,
  isQuote
}: Props): JSX.Element {
  if (!message.attachments?.length) {
    return null;
  }

  let data = message.attachments;

  if (isQuote && dataQuote) {
    data = data.filter(item => !item.message_id);
  }

  if (!data && !data.length) return null;

  const mediaItems = data.filter(item => item.image_url);

  // if it has multiple image (countimage > 1) => all attachment is image (mixed attachment not support this version)
  if (mediaItems.length > 1) {
    return (
      <MsgAttachmentMultiMedia mediaItems={mediaItems} isOwner={isOwner} />
    );
  }

  return data.map((item, i) => (
    <MsgAttachment
      {...item}
      key={`k${i}`}
      isOwner={isOwner}
      msgType={msgType}
    />
  )) as any;
}
