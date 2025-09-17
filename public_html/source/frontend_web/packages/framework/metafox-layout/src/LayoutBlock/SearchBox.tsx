/**
 * @type: ui
 * name: ui.searchBox
 */

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
  borderRadius: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center',
  backgroundColor: theme.palette.action.hover
}));

interface Props {
  value?: string;
  placeholder?: string;
  onQueryChange?: (value: string) => void;
  sx?: any;
  keyTab?: string;
}

export default function SearchBox({
  placeholder = 'search_dot',
  value,
  onQueryChange,
  sx,
  keyTab
}: Props) {
  const { i18n } = useGlobal();
  const [query, setQuery] = React.useState<string>(value);

  React.useEffect(() => {
    setQuery('');
  }, [keyTab]);

  const handleKeyDown = (evt: any) => {
    if (evt.keyCode === 13 && onQueryChange) {
      // enter
      onQueryChange(query);
    }
  };

  return (
    <SearchRoot data-testid="searchBoxRoot" sx={sx}>
      <InputBase
        variant="search"
        name="q"
        type="search"
        value={query}
        onChange={e => setQuery(e.target.value)}
        placeholder={i18n.formatMessage({ id: placeholder })}
        onKeyDown={handleKeyDown}
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
