import { LineIcon, MenuItemProps } from '@metafox/ui';
import {
  ListItemButton,
  ListItemIcon,
  ListItemSecondaryAction,
  ListItemText
} from '@mui/material';
import React from 'react';
import MenuItemAccordion from './MenuItemAccordion';
import MenuItemDivider from './MenuItemDivider';
import MenuItemPopover from './MenuItemPopover';
import MenuItemSwitch from './MenuItemSwitch';

export default function MenuItem(props: MenuItemProps) {
  const {
    as,
    icon,
    active,
    handleAction,
    params,
    disabled,
    value,
    iconSecondary,
    dense,
    label
  } = props;
  const onClick = () => handleAction(value, params);

  if ('popover' === as) return <MenuItemPopover {...props} />;

  if ('accordion' === as) return <MenuItemAccordion {...props} />;

  if ('divider' === as) return <MenuItemDivider />;

  if ('switch' === as) return <MenuItemSwitch onClick={onClick} {...props} />;

  return (
    <ListItemButton
      dense={dense}
      disabled={disabled}
      onClick={active ? undefined : onClick}
      disableRipple
      selected={active}
    >
      <ListItemIcon>
        <LineIcon icon={icon} />
      </ListItemIcon>
      <ListItemText primary={label} />
      {iconSecondary ? (
        <ListItemSecondaryAction>
          <LineIcon icon={iconSecondary} />
        </ListItemSecondaryAction>
      ) : null}
    </ListItemButton>
  );
}
