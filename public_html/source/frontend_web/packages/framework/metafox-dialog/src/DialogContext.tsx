import * as React from 'react';
import { DialogItemContext } from './types';

const DialogContext = React.createContext<DialogItemContext>({
  dialogProps: {
    open: false,
    onClose: void 0,
    onExit: void 0,
    onExited: void 0
  },
  closeDialog: void 0,
  setDialogValue: (value: unknown) => void 0,
  setUserConfirm: void 0
});

export default DialogContext;
