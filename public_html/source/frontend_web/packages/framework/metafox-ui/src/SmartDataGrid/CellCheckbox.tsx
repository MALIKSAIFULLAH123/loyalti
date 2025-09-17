/**
 * @type: ui
 * name: dataGrid.cell.Checkbox
 */
import { Checkbox } from '@mui/material';
import React from 'react';
import useDataGridContext from './useDataGridContext';

function CellCheckbox({ selected, id }) {
  const { apiRef } = useDataGridContext();

  const onClick = () => apiRef.current.toggleSelect(id);

  return (
    <Checkbox
      size="medium"
      checked={selected}
      color="primary"
      onClick={onClick}
    />
  );
}

export default CellCheckbox;
