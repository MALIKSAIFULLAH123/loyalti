import { LineIcon } from '@metafox/ui';
import { InputBase, InputBaseProps, styled } from '@mui/material';
import React from 'react';

const InputBaseStyled = styled(InputBase, { 
  name: 'LayoutSlot'
})(
  ({ theme }) => ({
    'input[type="search"]::-webkit-search-cancel-button': {
      WebkitAppearance: 'none'
    },
    '&.Mui-focused': {
      borderColor: theme.palette.primary.main
    }
  })
);

export default function SearchBox({
  placeholder,
  name = 'search',
  type = 'search',
  size = 'medium',
  autoComplete = 'off',
  'data-testid': testid = 'searchBox',
  fullWidth = true,
  endAdornment,
  ...props
}: InputBaseProps & { 'data-testid'?: string }) {
  return (
    <InputBaseStyled
      variant="search"
      name={name}
      type={type}
      fullWidth={fullWidth}
      size={size}
      startAdornment={<LineIcon icon="ico-search-o" />}
      endAdornment={endAdornment}
      placeholder={placeholder}
      autoComplete={autoComplete}
      aria-controls="search_complete"
      inputProps={{
        'aria-label': `${placeholder}`,
        'aria-autocomplete': 'list',
        'aria-expanded': true,
        'data-testid': testid,
        role: 'combobox',
        spellCheck: false
      }}
      {...props}
    />
  );
}
