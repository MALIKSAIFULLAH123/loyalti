import * as React from 'react';
import { LayoutPageStateShape } from './types';

const LayoutContext = React.createContext<LayoutPageStateShape>({
  layoutEditMode: 0,
  templateName: 'default',
  pageName: 'default',
  pageSize: 'large',
  blocks: [],
  pageParams: {},
  pageHelmet: {}
});

export default LayoutContext;
