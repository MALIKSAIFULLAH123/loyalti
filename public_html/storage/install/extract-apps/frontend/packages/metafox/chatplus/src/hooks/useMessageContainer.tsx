import React from 'react';
import { MessagesContext } from '@metafox/chatplus/context';

export default function useMessageContainer() {
  return React.useContext(MessagesContext);
}
