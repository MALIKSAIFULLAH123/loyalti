import * as React from 'react';
import SlotContext from './SlotContext';

export default function SlotProvider(props) {
  const value = React.useState({});

  return (
    <SlotContext.Provider value={value as any}>
      {props.children}
    </SlotContext.Provider>
  );
}
