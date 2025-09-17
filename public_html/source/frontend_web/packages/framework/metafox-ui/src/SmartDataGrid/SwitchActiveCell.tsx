/**
 * @type: ui
 * name: dataGrid.cell.SwitchActiveCell
 */
import { useGlobal } from '@metafox/framework';
import { Box, Switch, Tooltip } from '@mui/material';
import { get } from 'lodash';
import React from 'react';
import useDataGridContext from './useDataGridContext';

// todo moved this column to base size.
export default function SwitchActiveCell({
  id,
  row,
  colDef: { field, reload, action = 'toggleActive', fieldDisabled }
}) {
  const { handleRowAction } = useDataGridContext();
  const { i18n } = useGlobal();
  const value = get(row, field);
  const disabled = get(row, fieldDisabled);

  return (
    <Tooltip
      title={i18n.formatMessage({
        id: value === null || Boolean(value) ? 'activate' : 'deactivate'
      })}
      placement="top"
      key={`${id}${value}`}
    >
      <Box
        sx={{
          display: 'flex',
          height: '100%',
          alignItems: 'center'
        }}
      >
        <Box sx={{ display: 'inline-flex', width: 33 }}>
          <Switch
            size="small"
            disabled={Boolean(value === null || row._dirty || disabled)}
            checked={value === null || Boolean(value)}
            onChange={() =>
              handleRowAction('row/active', {
                action,
                field,
                id,
                row,
                reload
              })
            }
          />
        </Box>
      </Box>
    </Tooltip>
  );
}
