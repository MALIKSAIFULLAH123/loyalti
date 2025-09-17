/**
 * @type: ui
 * name: dataGrid.cell.DragHolderCell
 */
import { Box } from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import HeaderCell from './HeaderCell';

function DragHolderCell({ colDef }) {
  return (
    <HeaderCell colDef={colDef}>
      <Box mx={1}>
        <LineIcon icon="ico-arrows-move" />
      </Box>
    </HeaderCell>
  );
}

export default DragHolderCell;
