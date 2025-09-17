import { FormSchemaShape } from '@metafox/form';
import produce, { Draft } from 'immer';

type RowId = string | number;

interface State {
  loading: boolean;
  page?: number;
  appName: string;
  resourceName: string;
  pageSize: number;
  selection: RowId[];
  hideTitle?: boolean;
  searchFormVisible?: boolean;
  hideDescription?: boolean;
  hideToggleSearch?: boolean;
  title?: string;
  dense?: boolean;
  description?: string;
  rowMap: Record<RowId, any>;
  rowIds: RowId[];
  searchValues?: Record<string, any>;
  selectionCount?: number;
  searchForm?: FormSchemaShape;
}

type SingleRowAction =
  | 'row/delete'
  | 'row/put'
  | 'row/select'
  | 'row/patch'
  | 'row/toggleSelect';

type MultiRowAction = 'rows/delete' | 'rows/put' | 'rows/patch' | 'rows/select';

type UnknownAction = 'toggleSelectAll';

type Action =
  | { type: SingleRowAction; payload: any }
  | { type: MultiRowAction; payload: any }
  | { type: UnknownAction; payload?: any };

export default produce((draft: Draft<State>, { type, payload }: Action) => {
  switch (type) {
    case 'row/delete':
      delete draft.rowMap[payload];
      break;
    case 'rows/delete':
      (payload as RowId[]).forEach(id => delete draft[id]);
      break;
    case 'row/patch':
      break;
    case 'row/put':
      break;
    case 'rows/patch':
      break;
    case 'rows/put':
      break;
  }
});
