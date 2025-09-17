import React, { HTMLInputTypeAttribute } from 'react';
import { TextField, FormControl } from '@mui/material';

type Props = Readonly<{
  'data-test-id'?: string;
  label: string;
  onChange: (val: string) => void;
  placeholder?: string;
  value: string;
  type?: HTMLInputTypeAttribute;
  fullWidth?: boolean;
  margin?: 'normal' | 'dense' | 'none';
}>;

export default function TextInput({
  label,
  value,
  onChange,
  placeholder = '',
  'data-test-id': dataTestId,
  type = 'text',
  fullWidth = true,
  margin = 'normal'
}: Props): JSX.Element {
  return (
    <FormControl margin={margin} fullWidth={fullWidth}>
      <TextField
        label={label}
        type={type}
        InputLabelProps={{
          shrink: type === 'number' ? true : undefined
        }}
        placeholder={placeholder}
        value={value}
        onChange={e => {
          onChange(e.target.value);
        }}
        data-test-id={dataTestId}
      />
    </FormControl>
  );
}
