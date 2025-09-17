import * as React from 'react';
import { LayoutScope } from './types';

const LayoutScopeContext = React.createContext<LayoutScope>([false, false]);

export default LayoutScopeContext;
