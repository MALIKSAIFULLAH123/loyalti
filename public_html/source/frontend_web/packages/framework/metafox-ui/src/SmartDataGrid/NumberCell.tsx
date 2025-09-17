/**
 * @type: ui
 * name: dataGrid.cell.NumberCell
 */

import { get } from 'lodash';
import { RouteLink, useGlobal } from '@metafox/framework';
import React from 'react';

// todo moved this column to base size.
export default function NumberCell({
  id,
  row,
  colDef: { field, urlField, target }
}) {
  const { i18n } = useGlobal();

  if (urlField) {
    const url = get<string>(row, urlField);

    if (url) {
      return (
        <RouteLink target={target} to={url}>
          {i18n.formatNumber(get(row, field, 0))}
        </RouteLink>
      );
    }
  }

  return i18n.formatNumber(get(row, field, 0));
}
