import { SearchBox } from '@metafox/ui';
import { InputBaseProps } from '@mui/material';
import React from 'react';

export interface SearchInputProps extends InputBaseProps {
  onChanged: (value: string) => void;
}

export default function DialogSearchInput({
  placeholder,
  onChanged,
  ...rest
}: SearchInputProps) {
  return (
    <SearchBox
      data-testid="searchBox"
      placeholder={placeholder}
      onChange={evt => onChanged(evt.currentTarget.value)}
      {...rest}
    />
  );
}
