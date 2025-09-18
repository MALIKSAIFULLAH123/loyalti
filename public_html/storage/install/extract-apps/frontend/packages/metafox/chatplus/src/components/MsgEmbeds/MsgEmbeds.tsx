import { MsgItemShape } from '@metafox/chatplus/types';
import React from 'react';
import MsgEmbed from './MsgEmbed';

interface Props {
  message: MsgItemShape;
}

export default function MsgEmbeds({ message }: Props): JSX.Element {
  if (!message.urls?.length) return null;

  return message.urls
    .filter(item => !item?.ignoreParse && item.meta && item.parsedUrl)
    .map((item, i) => <MsgEmbed {...item} key={`k${i}`} />) as any;
}
