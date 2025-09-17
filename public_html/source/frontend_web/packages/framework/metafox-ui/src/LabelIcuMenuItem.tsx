/**
 * @type: ui
 * name: menuItem.dataGird.as.labelIcu
 * chunkName: menuItemAs
 */
import { ControlMenuItemProps } from '@metafox/ui';
import { compactUrl } from '@metafox/utils';
import { MenuItem } from '@mui/material';
import { get, pick } from 'lodash';
import React from 'react';

export default function SideBarButtonMenuItem(
  props: ControlMenuItemProps & {
    row?: any;
  }
) {
  const { item, onClick = (e, item: any) => {}, row } = props || {};

  const paramAsItem: any = Object.values(get(item, 'params.as') || {});
  const params = pick(row, paramAsItem);

  const label = compactUrl(item?.label, params);

  return <MenuItem onClick={evt => onClick(evt, item)}>{label}</MenuItem>;
}
