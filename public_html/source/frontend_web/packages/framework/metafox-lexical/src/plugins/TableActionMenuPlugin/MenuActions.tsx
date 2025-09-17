import { LineIcon, PopoverContext } from '@metafox/ui';
import { Popover, IconButton } from '@mui/material';
import React from 'react';

type Props = {
  icon: string;
  isMenuOpen: boolean;
  label: string;
  children: React.ReactNode;
  disabled: boolean;
  active: boolean;
  setIsMenuOpen: React.Dispatch<React.SetStateAction<boolean>>;
};

export default function MenuItemPopover(props: Props) {
  const {
    icon,
    active,
    disabled,
    children,
    label,
    isMenuOpen: open,
    setIsMenuOpen: setOpen
  } = props;
  const anchorRef = React.useRef();
  const handleClose = () => setOpen(false);
  const handleToggle = () => setOpen(prev => !prev);

  return (
    <>
      <IconButton
        aria-label={label}
        size="small"
        onClick={handleToggle}
        role="button"
        disabled={disabled}
        selected={active}
        ref={anchorRef}
        sx={{ width: '24px', height: '24px', fontSize: '12px' }}
      >
        <LineIcon icon={icon} />
      </IconButton>
      <Popover anchorEl={anchorRef.current} open={open} onClose={handleClose}>
        <PopoverContext.Provider value={{ closePopover: handleClose }}>
          {children}
        </PopoverContext.Provider>
      </Popover>
    </>
  );
}
