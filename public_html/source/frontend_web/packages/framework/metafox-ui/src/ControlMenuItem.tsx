import React from 'react';
import { ControlMenuItemProps } from './types';

export default function ControlMenuItem({ as, ...rest }: ControlMenuItemProps) {
  if (!as) return null;

  return React.createElement(as, rest);
}
