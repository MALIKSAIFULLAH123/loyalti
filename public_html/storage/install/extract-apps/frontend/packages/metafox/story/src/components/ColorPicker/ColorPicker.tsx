import { Box, Popover, styled, TextField } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import ColorPopover from './ColorPopover';
import { useGlobal } from '@metafox/framework';

const ColorPickerAdornment = styled(Box, {
  name: 'MuiButton',
  slot: 'ColorPickerAdornment'
})<{ color: string }>(({ color }) => ({
  width: 28,
  height: 28,
  border: '1px solid rgba(0,0,0,0.1)',
  backgroundColor: color,
  cursor: 'pointer'
}));

type ConfigProps = {
  item: any;
  updateItem: (data) => void;
  defaultValue?: string;
  picker?: string;
};

const ColorPicker = (config: ConfigProps) => {
  const { i18n } = useGlobal();
  const name = 'color-picker';
  const [open, setOpen] = React.useState<boolean>(false);
  const controlRef = React.useRef();

  const { defaultValue = '#fff', picker = 'chrome', item, updateItem } = config;

  const handleBlur = e => {};

  const handleChange = value => {
    updateItem && updateItem({ color: value });
  };

  return (
    <>
      <TextField
        ref={controlRef}
        value={item?.color ?? defaultValue}
        onChange={e => handleChange(e.currentTarget.value)}
        size={'medium'}
        onBlur={handleBlur}
        onClick={() => setOpen(true)}
        InputProps={{
          readOnly: true,
          endAdornment: (
            <ColorPickerAdornment
              color={item?.color ?? defaultValue}
              onClick={() => setOpen(true)}
            />
          )
        }}
        inputProps={{
          'data-testid': camelCase(`input ${name}`)
        }}
        label={i18n.formatMessage({ id: 'color' })}
        placeholder={i18n.formatMessage({ id: 'select_color' })}
        sx={{ width: '100%' }}
      />
      <Popover
        disablePortal={false}
        open={open}
        anchorEl={controlRef.current}
        onClose={() => setOpen(false)}
        style={{ zIndex: 9999 }}
        anchorOrigin={{
          vertical: 'bottom'
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

export default ColorPicker;
