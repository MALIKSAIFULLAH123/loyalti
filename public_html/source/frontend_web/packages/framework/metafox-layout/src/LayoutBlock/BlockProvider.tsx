import { BlockViewProps } from '@metafox/framework';
import React from 'react';
import BlockContext from './BlockContext';

export interface BlockProviderProps {
  value: BlockViewProps;
  component: React.ElementType;
}

export default function BlockProvider({
  value,
  component
}: BlockProviderProps) {
  // re-assign value to render box

  return React.createElement(
    BlockContext.Provider,
    { value },
    React.createElement(component, value)
  );
}
