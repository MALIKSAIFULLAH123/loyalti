import { Box } from '@mui/material';
import { hexToRgb, rgbToHex } from '@mui/system/colorManipulator';
import React from 'react';
import { HexAlphaColorPicker } from 'react-colorful';

interface Props {
  color: string;
  onChange?: (value: string) => void;
  picker: string;
}
const defaultColor = '#ffffff';

function ColorPopover({ color: value = '#000', onChange }: Props) {
  try {
    value = /#/i.test(value) ? value : rgbToHex(value);
  } catch (err) {
    // color error.
  }

  const hexColorRegex = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;

  const [color, setColor] = React.useState<string>(value);
  const [input, setInput] = React.useState<string>(value);

  const handleChange = React.useCallback((value: string) => {
    setInput(value);

    if (hexColorRegex.test(value)) {
      setColor(value);
    }
  }, []);

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

  const onBlur = e => {
    const value = e.currentTarget.value;

    if (!hexColorRegex.test(value)) {
      handleChange(color || defaultColor);
    }
  };

  return (
    <Box sx={{ p: 2 }}>
      <HexAlphaColorPicker color={color} onChange={setColor} />
      <Box sx={{ pt: 1 }}>
        <input
          defaultValue={input}
          value={input}
          name="hex_alpha"
          placeholder="#aa00ccff"
          onChange={evt => handleChange(evt.currentTarget.value)}
          onBlur={onBlur}
        />
      </Box>
    </Box>
  );
}

export default ColorPopover;
