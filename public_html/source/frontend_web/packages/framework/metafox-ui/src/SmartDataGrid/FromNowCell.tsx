/**
 * @type: ui
 * name: dataGrid.cell.FromNowCell
 */

import { FromNow } from '@metafox/ui';
import { get } from 'lodash';
import React from 'react';

export default function FromNowCell({ row, colDef: { field } }) {
  return <FromNow value={get(row, field)} />;
}
