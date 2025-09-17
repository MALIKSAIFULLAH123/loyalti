/**
 * @type: ui
 * name: dataGrid.header.Checkbox
 */
import { Checkbox } from '@mui/material';
import React from 'react';
import HeaderCell from './HeaderCell';
import useDataGridContext from './useDataGridContext';
import useGridDataState from './useGridDataState';

function HeaderCheckbox({ colDef }) {
  const { apiRef } = useDataGridContext();
  const { indeterminate, checked } = useGridDataState();

  const handleToggleSelectAll = React.useCallback(
    () => apiRef.current.toggleSelectAll(),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );
  // const indeterminate =
  //   selection.length !== rows.length && selection.length > 0;

  // const checked = selection.length > 0;

  return (
    <HeaderCell colDef={colDef}>
      <Checkbox
        size="medium"
        disabled={false}
        onClick={handleToggleSelectAll}
        checked={checked}
        indeterminate={indeterminate}
        color="primary"
      />
    </HeaderCell>
  );
}

export default HeaderCheckbox;
