import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, InputBase } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const SearchRoot = styled(Box, {
  name: 'MuiLayoutSearchBox',
  slot: 'Root'
})(({ theme }) => ({
  height: theme.spacing(5),
  display: 'flex',
  alignItems: 'center',
  margin: theme.spacing(1.5, 2)
}));

interface Props {
  value?: string;
  placeholder?: string;
  onQueryChange?: (value: string) => void;
  sx?: any;
  keyTab?: string;
}

export default function SearchMember({
  placeholder = 'chatplus_search_for_members',
  value,
  onQueryChange,
  sx
}: Props) {
  const { i18n } = useGlobal();
  const [query, setQuery] = React.useState<string>(value);

  const handleChange = (e: any) => {
    const value = e.target.value;
    setQuery(value);

    if (onQueryChange) {
      onQueryChange(value);
    }
  };

  return (
    <SearchRoot sx={sx}>
      <InputBase
        variant="search"
        name="q"
        type="search"
        value={query}
        onChange={handleChange}
        placeholder={i18n.formatMessage({ id: placeholder })}
        autoComplete="off"
        startAdornment={<LineIcon icon="ico-search-o" />}
        aria-controls="search_complete"
        inputProps={{
          'aria-label': `${i18n.formatMessage({ id: placeholder })}`,
          'aria-autocomplete': 'list',
          'aria-expanded': true,
          'data-testid': 'search box',
          role: 'combobox',
          spellCheck: false
        }}
        sx={{ width: '100%' }}
      />
    </SearchRoot>
  );
}
