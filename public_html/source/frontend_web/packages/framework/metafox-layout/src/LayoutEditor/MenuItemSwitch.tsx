import { LineIcon, MenuItemProps } from '@metafox/ui';
import {
  Switch,
  ListItemButton,
  ListItemIcon,
  ListItemSecondaryAction,
  ListItemText
} from '@mui/material';
import React from 'react';

export default function MenuItemAccordion(props: MenuItemProps) {
  const { label, icon, onClick, active, dense, disabled } = props;

  return (
    <ListItemButton
      dense={dense}
      disabled={disabled}
      onClick={onClick}
      disableRipple
    >
      <ListItemIcon>
        <LineIcon icon={icon} />
      </ListItemIcon>
      <ListItemText primary={label} />
      <ListItemSecondaryAction>
        <Switch size="small" checked={Boolean(active)} />
      </ListItemSecondaryAction>
    </ListItemButton>
  );
}
