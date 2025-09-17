/**
 * @type: ui
 * name: dataGrid.cell.NumeralCell
 */

import { get, isNumber } from 'lodash';
import numeral from 'numeral';

export default function NumeralCell({ row, colDef: { field, format = '0b' } }) {
  const value = get(row, field);

  if (!value) {
    return null;
  }

  if (!isNumber(value)) {
    return value;
  }

  return numeral(value).format(format);
}
