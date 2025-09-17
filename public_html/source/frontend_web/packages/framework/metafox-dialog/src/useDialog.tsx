/**
 * @type: service
 * name: useDialog
 */
import * as React from 'react';
import DialogContext from './DialogContext';
import { DialogItemContext } from './types';

export default function useDialog(): DialogItemContext {
  return React.useContext(DialogContext);
}
