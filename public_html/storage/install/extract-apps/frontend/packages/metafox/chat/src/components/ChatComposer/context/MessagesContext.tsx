import React from 'react';

const MessagesContext = React.createContext<
  React.MutableRefObject<HTMLDivElement>
>(React.createRef());

export default MessagesContext;
