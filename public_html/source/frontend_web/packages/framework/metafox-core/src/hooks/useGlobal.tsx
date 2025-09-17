/**
 * @type: service
 * name: useGlobal
 */
import * as React from 'react';
import GlobalContext from '../GlobalContext';
import { Manager } from '../Manager';

export default function useGlobal(): Manager {
  return React.useContext(GlobalContext);
}
