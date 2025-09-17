import * as React from 'react';

export type TPopoverContext = {
  closePopover: () => void;
};

const PopoverContext = React.createContext<TPopoverContext>({
  closePopover: void 0
});

export default PopoverContext;
