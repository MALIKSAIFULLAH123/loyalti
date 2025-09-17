/**
 * @type: ui
 * name: menuItem.as.sidebarHeading
 * chunkName: menuItemAs
 */
import { ControlMenuItemProps } from '@metafox/ui';
import React from 'react';
import { ListSubheader, styled } from '@mui/material';

const List = styled(ListSubheader, {
  name: 'List'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18),
  lineHeight: 2,
  fontWeight: 'bold',
  color: theme.palette.text.primary,
  margin: theme.spacing(1, 0)
}));

export default function SideBarHeadingMenuItem(props: ControlMenuItemProps) {
  const { item, classes } = props;

  return (
    <List
      className={classes.menuHeading}
      data-testid={item.testid}
      disableSticky
    >
      {item.label}
    </List>
  );
}
