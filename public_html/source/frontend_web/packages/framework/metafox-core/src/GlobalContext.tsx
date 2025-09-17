import * as React from 'react';
import { Manager } from './Manager';

const GlobalContext = React.createContext<Manager>(Manager.factory({}));

export const GlobalProvider = GlobalContext.Provider;

export default GlobalContext;
