import { Box, styled } from '@mui/material';
import { hexToRgb, rgbToHex } from '@mui/system/colorManipulator';
import React from 'react';
import { HexColorPicker } from 'react-colorful';

const ColorPickerAdornment = styled(Box, {
  name: 'MuiButton',
  slot: 'ColorPickerAdornment'
})<{ color: string }>(({ theme, color }) => ({
  width: 26,
  height: 26,
  border: '1px solid rgba(0,0,0,0.1)',
  borderRadius: theme.shape.borderRadius,
  backgroundColor: color,
  cursor: 'pointer',
  margin: theme.spacing(0.4)
}));

const BasicColor = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexWrap: 'wrap',
  marginTop: theme.spacing(2)
}));

interface Props {
  color: string;
  onChange?: (value: string) => void;
  picker: string;
}

const basicColors = [
  '#fff',
  '#000',
  '#2a86ae',
  '#702928',
  '#f6724a',
  '#f1c43a',
  '#8e939c',
  '#89be4c',
  '#2b457c',
  '#f83d3c',
  '#dcd3ef',
  '#fb3ea0'
];

function ColorPopover({ color: value = '#000', onChange }: Props) {
  try {
    value = /#/i.test(value) ? value : rgbToHex(value);
  } catch (err) {
    // color error.
  }

  const [color, setColor] = React.useState<string>(value);

  React.useEffect(() => {
    try {
      // required to check color is valid
      hexToRgb(color);
      onChange(color);
    } catch (err) {
      //
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [color]);

  return (
    <Box sx={{ p: 2, maxWidth: '230px' }}>
      <HexColorPicker
        color={color}
        onChange={setColor}
        style={{ height: '150px' }}
      />
      <BasicColor>
        {basicColors.map(item => (
          <ColorPickerAdornment
            key={item}
            color={item ?? color}
            onClick={() => setColor(item)}
          />
        ))}
      </BasicColor>
    </Box>
  );
}

export default ColorPopover;
