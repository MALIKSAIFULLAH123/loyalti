/**
 * @type: ui
 * name: dataGrid.cell.SortIconCell
 */
import { Box } from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import { get } from 'lodash';

function CellCheckbox({ row, colDef: { field, icon = 'ico-arrows-move' } }) {
  const sx = get(row, 'sx');
  const sxProps = get(sx, field);

  return (
    <Box sx={sxProps}>
      <LineIcon icon={icon} />
    </Box>
  );
}

export default CellCheckbox;
