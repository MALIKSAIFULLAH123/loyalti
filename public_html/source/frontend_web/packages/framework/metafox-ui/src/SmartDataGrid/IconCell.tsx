/**
 * @type: ui
 * name: dataGrid.cell.IconCell
 */

import { LineIcon } from '@metafox/ui';
import { get } from 'lodash';
import React from 'react';

// todo moved this column to base size.
export default function IconCell({ row, colDef: { field, sx: sxProps } }) {
  const icon = get(row, field);

  if (!icon) return null;

  return <LineIcon icon={icon} sx={sxProps} />;
}
