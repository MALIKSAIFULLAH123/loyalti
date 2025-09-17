/**
 * @type: ui
 * name: menuItem.as.divider
 * chunkName: menuItemAs
 */
import { Divider } from '@mui/material';
import React from 'react';

export default function DividerMenuItem({
  item,
  variant = 'middle',
  light,
  className
}: any) {
  return (
    <Divider
      variant={item?.variant || variant}
      light={item?.light || light}
      className={className}
    />
  );
}
