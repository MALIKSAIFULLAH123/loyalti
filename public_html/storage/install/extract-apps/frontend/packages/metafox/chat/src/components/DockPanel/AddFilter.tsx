import { SearchBox } from '@metafox/ui';
import { Box, Button, styled } from '@mui/material';
import React from 'react';

const Root = styled('div', { name: 'ChatDockFilter' })(({ theme }) => ({
  position: 'relative'
}));
const SearchControl = styled(Box, { name: 'ChatDockFilter' })(({ theme }) => ({
  padding: theme.spacing(1, 1, 1, 2),
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center'
}));

const SearchWrapper = styled(SearchBox, {
  name: 'SearchWrapper'
})(({ theme }) => ({
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

const ButtonCustom = styled(Button, { name: 'ChatDockFilter' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(0.5)
  })
);

interface Props {
  placeholder: string;
  size: number;
  hide?: boolean;
  disabled?: boolean;
  onKeyUp: (evt: React.KeyboardEvent) => void;
  valueSearch?: string;
  onChangeSearch?: (evt: any) => void;
  label?: string;
  onSubmit?: () => void;
  variant?: 'group' | 'direct' | 'new-mesasage' | string;
  isShowButtonSubmit?: boolean;
  sx?: any;
}

export default function AddFilter({
  placeholder,
  size,
  hide,
  label,
  disabled,
  valueSearch,
  onChangeSearch,
  onKeyUp,
  onSubmit,
  variant = 'direct',
  isShowButtonSubmit = true,
  sx
}: Props) {
  if (hide) return null;

  return (
    <Root>
      <SearchControl sx={sx}>
        <SearchWrapper
          placeholder={placeholder}
          onKeyUp={onKeyUp}
          value={valueSearch}
          onChange={onChangeSearch}
        />
        {isShowButtonSubmit && onSubmit ? (
          <ButtonCustom
            disabled={disabled}
            size="medium"
            color="primary"
            variant="text"
            onClick={onSubmit}
          >
            {label}
          </ButtonCustom>
        ) : null}
      </SearchControl>
    </Root>
  );
}
