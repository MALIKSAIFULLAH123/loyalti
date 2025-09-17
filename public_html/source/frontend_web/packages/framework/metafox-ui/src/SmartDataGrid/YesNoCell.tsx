/**
 * @type: ui
 * name: dataGrid.cell.YesNoCell
 */

import { useGlobal } from '@metafox/framework';
import { get } from 'lodash';
import React from 'react';

export default function YesNoCell({ row, colDef: { field } }) {
  const { i18n } = useGlobal();

  return (
    <span>{i18n.formatMessage({ id: get(row, field, 0) ? 'yes' : 'no' })}</span>
  );
}
