/* eslint-disable eqeqeq */
import produce, { Draft } from 'immer';
import { escapeRegExp, includes } from 'lodash';
import { GridDataState } from '../types';

function validateDraft(draft: Draft<GridDataState>) {
  draft.indeterminate =
    draft.selection.length != draft.rows.length && draft.selection.length > 0;

  draft.selectionCount = draft.selection.length;
  draft.checked = draft.selectionCount > 0;
}

export default produce((draft: Draft<GridDataState>, action: any) => {
  const { payload } = action;

  switch (action.type) {
    case 'removeRow': {
      const { id } = payload;
      const index = draft.rows.findIndex(row => draft.getRowId(row) == id);
      draft.rows.splice(index, 1);
      draft.allRows.splice(index, 1);
      break;
    }

    case 'patchRow':
      {
        const { id, data } = payload;
        const index = draft.rows.findIndex(row => draft.getRowId(row) == id);
        draft.rows[index] = { ...draft.rows[index], ...(data as object) };
        draft.allRows[index] = { ...draft.rows[index], ...(data as object) };
      }
      break;
    case 'setRow':
      {
        const { id, data } = payload;
        const index = draft.rows.findIndex(row => draft.getRowId(row) == id);
        draft.rows[index] = data;
        draft.allRows[index] = data;
      }
      break;
    case 'setRows': {
      draft.rows = payload.rows;
      draft.paging = payload.paging;
      break;
    }
    case 'initRows': {
      draft.rows = payload.rows;
      draft.paging = payload.paging;
      draft.allRows = payload.rows;
      break;
    }
    case 'removeRows': {
      const { id } = payload;
      draft.rows = draft.rows.filter(row => !includes(id, draft.getRowId(row)));
      break;
    }

    case 'patchMultiRows': {
      const idField = draft.idField;

      payload.forEach(({ [idField]: id, ...mix }) => {
        const index = draft.rows.findIndex(row => row[idField] == id);

        if (index > -1) {
          draft.rows[index] = { ...draft.rows[index], ...mix };
        }
      });
      break;
    }

    case 'patchRows': {
      const { id, data } = payload;

      draft.rows.forEach((row, index) => {
        if (id.includes(draft.getRowId(row))) {
          draft.rows[index] = { ...draft.rows[index], ...data };
        }
      });
      break;
    }
    case 'toggleSelect': {
      const { id } = payload;
      const index = draft.selection.findIndex(x => x == id);

      if (index < 0) {
        // not found
        draft.selection.push(id);
      } else {
        draft.selection.splice(index, 1);
      }

      validateDraft(draft);

      break;
    }

    case 'removeSelect': {
      const { id } = payload;

      if (!draft?.selection?.length) break;

      const index = draft.selection.findIndex(x => x == id);

      if (index >= 0) {
        draft.selection.splice(index, 1);
      }

      validateDraft(draft);

      break;
    }

    case 'toggleSelectAll': {
      if (draft.selection.length) {
        draft.selection = [];
      } else {
        draft.selection = draft.rows.map(row => draft.getRowId(row));
      }

      validateDraft(draft);

      break;
    }
    case 'orderRows': {
      const { order_ids } = payload;

      draft.rows = order_ids.map((id: number) =>
        draft.rows.find(x => x.id === id)
      );

      break;
    }
    case 'refresh': {
      draft.loadRev = (draft.loadRev ?? 1) + 1;
      draft.selection = [];
      validateDraft(draft);
      break;
    }

    case 'refreshSelection': {
      draft.selection = [];

      validateDraft(draft);

      break;
    }
    case 'search': {
      const query = action.payload;

      const names = draft.config.inlineSearch;

      const match = (row: Record<string, string>): string => {
        return names.find(name =>
          new RegExp(escapeRegExp(query), 'ig').test(row[name])
        );
      };

      draft.rows = draft.raws.filter(match);
    }
  }
});
