import * as React from 'react';
import { LayoutSlotContextShape } from '../types';

const SlotContext = React.createContext<LayoutSlotContextShape>([
  {},
  () => ({})
]);
export default SlotContext;
