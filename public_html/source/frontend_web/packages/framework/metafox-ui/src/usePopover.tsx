/**
 * @type: service
 * name: usePopover
 */
import * as React from 'react';
import PopoverContext from './PopoverContext';

export default function usePopover() {
  return React.useContext(PopoverContext);
}
