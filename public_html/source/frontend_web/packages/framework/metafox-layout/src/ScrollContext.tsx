import React from 'react';

const ScrollContext =
  React.createContext<React.MutableRefObject<HTMLElement>>();

export default ScrollContext;
