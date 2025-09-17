/**
 * @type: ui
 * name: dataGrid.cell.DateCell
 */

import { useGlobal } from '@metafox/framework';
import { get } from 'lodash';

export default function BasicCell({
  row,
  colDef: { field, format = 'lll', showTime = false }
}) {
  const { i18n } = useGlobal();

  const value = get(row, field);

  if (!value) {
    return null;
  }

  if (showTime) {
    return `${i18n.formatDate(value, { format })} ${i18n.formatTime(value, {
      format
    })}`;
  }

  return i18n.formatDate(value, { format });
}
