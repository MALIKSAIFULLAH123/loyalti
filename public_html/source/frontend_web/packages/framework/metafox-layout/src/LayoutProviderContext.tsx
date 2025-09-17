import * as React from 'react';
import { LayoutUpdate } from './types';

const LayoutProviderContext = React.createContext<LayoutUpdate>(void 0);

export default LayoutProviderContext;
