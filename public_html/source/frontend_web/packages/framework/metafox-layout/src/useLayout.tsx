/**
 * @type: service
 * name: useLayout
 */
import * as React from 'react';
import LayoutContext from './LayoutContext';
import { LayoutPageStateShape } from './types';

export default function useLayout(): LayoutPageStateShape {
  return React.useContext(LayoutContext);
}
