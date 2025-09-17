import { useGlobal } from '@metafox/framework';
import { LineIcon, MenuItemProps, PopoverContext } from '@metafox/ui';
import {
  ListItemButton,
  ListItemIcon,
  ListItemSecondaryAction,
  ListItemText,
  Popover
} from '@mui/material';
import React from 'react';

export default function MenuItemPopover(props: MenuItemProps) {
  const { jsxBackend } = useGlobal();
  const {
    icon,
    content,
    dense,
    label,
    active,
    iconSecondary,
    disabled,
    labelSub
  } = props;
  const [open, setOpen] = React.useState<boolean>(false);
  const anchorRef = React.useRef();
  const handleClose = () => setOpen(false);
  const handleToggle = () => setOpen(prev => !prev);

  return (
    <>
      <ListItemButton
        dense={dense}
        disabled={disabled}
        onClick={handleToggle}
        selected={active}
        disableRipple
        ref={anchorRef}
      >
        <ListItemIcon>
          <LineIcon icon={icon} />
        </ListItemIcon>
        <ListItemText primary={label} secondary={labelSub} />
        <ListItemSecondaryAction>
          {<LineIcon icon={iconSecondary} />}
        </ListItemSecondaryAction>
      </ListItemButton>
      <Popover
        anchorEl={anchorRef.current}
        id={open ? 'openPopup' : undefined}
        open={open}
        onClose={handleClose}
        sx={{ zIndex: 1500 }}
      >
        <PopoverContext.Provider value={{ closePopover: handleClose }}>
          {jsxBackend.render(content)}
        </PopoverContext.Provider>
      </Popover>
    </>
  );
}
