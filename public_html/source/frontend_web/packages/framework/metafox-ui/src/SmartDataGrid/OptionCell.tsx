/**
 * @type: ui
 * name: dataGrid.cell.OptionCell
 */
import { DropdownMenu } from '@metafox/ui';
import React from 'react';
import useDataGridContext from './useDataGridContext';

export default function OptionCell({ row, id }) {
  const { config, handleRowAction, classes } = useDataGridContext();

  return (
    <DropdownMenu
      iconClass={classes.optionIcon}
      buttonClass={classes.optionButton}
      handleAction={handleRowAction}
      size="small"
      item={row}
      id="itemOption"
      data={{ id, row }}
      items={config.itemActionMenu?.items}
    />
  );
}
