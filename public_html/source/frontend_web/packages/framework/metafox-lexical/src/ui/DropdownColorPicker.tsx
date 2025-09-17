import React, { ReactNode } from 'react';
import ColorPicker from './ColorPicker';
import DropDown from './DropDown';
import { Box } from '@mui/material';

type Props = {
  disabled?: boolean;
  buttonAriaLabel?: string;
  buttonLabel?: string;
  buttonIcon?: ReactNode;
  title?: string;
  stopCloseOnClickSelf?: boolean;
  color: string;
  onChange?: (color: string, skipHistoryStack: boolean) => void;
};

export default function DropdownColorPicker({
  disabled = false,
  stopCloseOnClickSelf = true,
  color,
  onChange,
  ...rest
}: Props) {
  return (
    <DropDown
      {...rest}
      disabled={disabled}
      stopCloseOnClickSelf={stopCloseOnClickSelf}
    >
      <Box p={2}>
        <ColorPicker color={color} onChange={onChange} />
      </Box>
    </DropDown>
  );
}
