/**
 * @type: service
 * name: useSmartDataGrid
 */
import { AppResource } from '@metafox/framework/Manager';
import React from 'react';
import { GridDataState } from '../types';
import reducer from './reducer';
import SmartDataGridApi from './SmartDataGridApi';

export default function useSmartDataGrid(
  config: AppResource,
  initState: GridDataState
): [
  React.ReducerState<any>,
  React.Dispatch<React.ReducerAction<any>>,
  React.RefObject<SmartDataGridApi>
] {
  const [state, dispatch] = React.useReducer(reducer, initState);
  const apiRef = React.useRef<SmartDataGridApi>(
    new SmartDataGridApi({ config, dispatch })
  );

  return [state, dispatch, apiRef];
}
