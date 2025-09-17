/**
 * @type: ui
 * name: menuItem.as.sidebarDivider
 * chunkName: menuItemAs
 */
import { ControlMenuItemProps } from '@metafox/ui';
import { Divider } from '@mui/material';
import React from 'react';

export default function SidebarDividerMenuItem(props: ControlMenuItemProps) {
  const { item } = props;

  return <Divider {...item.dividerProps} data-testid={item.testid} />;
}
