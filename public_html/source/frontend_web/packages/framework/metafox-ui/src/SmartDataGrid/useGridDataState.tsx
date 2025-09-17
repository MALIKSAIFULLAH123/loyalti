import { GridDataState } from '@metafox/framework';
import React from 'react';
import GridDataStateContext from './GridStateContext';

export default function useGridDataState() {
  return React.useContext<GridDataState>(GridDataStateContext);
}
