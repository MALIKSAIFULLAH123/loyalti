/**
 * @type: ui
 * name: dataGrid.cell.DateTimeCell
 */

import { useGlobal } from '@metafox/framework';
import { get } from 'lodash';

// full, long, medium, short
const maps = {
  full: { month: '2-digit', day: '2-digit', year: 'numeric' },
  medium: {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric'
  }
};

export default function DateTimeCell({
  row,
  colDef: { field, format = 'full' }
}) {
  const { i18n } = useGlobal();

  const value = get(row, field);

  if (!value) {
    return null;
  }

  return i18n.formatDate(value, maps[format]);
}
