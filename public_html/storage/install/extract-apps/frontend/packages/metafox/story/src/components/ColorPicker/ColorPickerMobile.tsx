import { Box, Popover, styled } from '@mui/material';
import React from 'react';
import ColorPopover from './ColorPopover';
import { LineIcon } from '@metafox/ui';

const ColorPickerAdornment = styled(Box, {
  name: 'MuiButton',
  slot: 'ColorPickerAdornment'
})<{ color: string }>(({ theme, color }) => ({
  width: '40px',
  height: '40px',
  borderRadius: '50%',
  backgroundColor: color,
  cursor: 'pointer',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  ' span': {
    color: '#000',
    fontSize: theme.mixins.pxToRem(24)
  }
}));

type ConfigProps = {
  item: any;
  updateItem: (data) => void;
  defaultValue?: string;
  picker?: string;
};

const ColorPickerMobile = (config: ConfigProps) => {
  const [open, setOpen] = React.useState<boolean>(false);
  const controlRef = React.useRef();

  const { defaultValue = '#fff', picker = 'chrome', item, updateItem } = config;

  const handleChange = value => {
    updateItem && updateItem({ color: value });
  };

  return (
    <>
      <ColorPickerAdornment
        ref={controlRef}
        color={item?.color ?? defaultValue}
        onClick={() => setOpen(true)}
      >
        <LineIcon icon="ico-color-palette" />
      </ColorPickerAdornment>
      <Popover
        disablePortal={false}
        open={open}
        anchorEl={controlRef.current}
        onClose={() => setOpen(false)}
        style={{ zIndex: 9999 }}
        anchorOrigin={{
          vertical: 'center',
          horizontal: 'left'
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right'
        }}
      >
        <ColorPopover
          color={item?.color ?? defaultValue}
          onChange={handleChange}
          picker={picker}
        />
      </Popover>
    </>
  );
};

export default ColorPickerMobile;
