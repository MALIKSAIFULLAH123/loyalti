import { AppResource } from '@metafox/framework/Manager';
import { GridRowData, GridRowId, SmartGridApi } from '../types';

export default class SmartDataGridApi implements SmartGridApi {
  private dispatch: (action: { type: string; payload: unknown }) => void;
  public config: AppResource;

  constructor({
    dispatch,
    config
  }: {
    config: AppResource;
    dispatch: (action: { type: string; payload: unknown }) => void;
  }) {
    this.config = config;
    this.dispatch = dispatch;
  }

  setRow = (id: GridRowId, data: GridRowData): void => {
    this.dispatch({ type: 'setRow', payload: { id, data } });
  };

  patchRow = (id: GridRowId, data: Record<string, any>): void => {
    this.dispatch({ type: 'patchRow', payload: { id, data } });
  };

  removeRow = (id: GridRowId): void => {
    this.dispatch({ type: 'removeRow', payload: { id } });
  };

  setRows = (rows: unknown, paging?: unknown): void => {
    this.dispatch({ type: 'setRows', payload: { rows, paging } });
  };

  patchRows = (id: GridRowId[], data: GridRowData): void => {
    this.dispatch({ type: 'patchRows', payload: { id, data } });
  };

  patchMultiRows = (data: GridRowData[]): void => {
    this.dispatch({ type: 'patchMultiRows', payload: data });
  };

  removeRows = (id: GridRowId[]): void => {
    this.dispatch({ type: 'removeRows', payload: { id } });
  };

  toggleSelect = (id: GridRowId): void => {
    this.dispatch({ type: 'toggleSelect', payload: { id } });
  };

  removeSelect = (id: GridRowId): void => {
    this.dispatch({ type: 'removeSelect', payload: { id } });
  };

  toggleSelectAll = (): void => {
    this.dispatch({ type: 'toggleSelectAll', payload: {} });
  };

  orderRows = (order_ids: GridRowData[]): void => {
    this.dispatch({ type: 'orderRows', payload: { order_ids } });
  };

  initRows = (rows: unknown, paging?: unknown): void => {
    this.dispatch({ type: 'initRows', payload: { rows, paging } });
  };

  /**
   * refresh rows
   */
  refresh = (): void => {
    this.dispatch({ type: 'refresh', payload: {} });
  };

  /**
   * refresh selection
   */
  refreshSelection = (): void => {
    this.dispatch({ type: 'refreshSelection', payload: {} });
  };
}
