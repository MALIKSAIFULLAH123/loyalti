import { RoomItemShape } from '@metafox/chat/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { InputBase, styled } from '@mui/material';
import { debounce } from 'lodash';
import React from 'react';

const SearchContainer = styled('div')(() => ({
  width: '100%'
}));

const SearchInput = styled(InputBase, {
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  maxWidth: '600px',
  height: theme.spacing(5),
  margin: theme.spacing(2),
  padding: theme.spacing(1.5, 2),
  borderRadius: theme.spacing(2.5),
  border: theme.mixins.border('secondary'),
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['700']
      : theme.palette.grey['100'],
  ...(isMobile && {
    margin: 0,
    width: '100%',
    maxWidth: '100%',
    padding: theme.spacing(0, 1),
    '& input': {
      paddingTop: theme.spacing(0.25)
    }
  }),
  minWidth: 250,
  [theme.breakpoints.down('sm')]: {
    minWidth: '100%'
  }
}));
const IconSearch = styled(LineIcon, {
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  color: theme.palette.text.primary,
  transition: 'all .2s ease',
  cursor: 'pointer',
  ...(isMobile && {
    padding: theme.spacing(1)
  })
}));

const OptionLabel = styled('div', {
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  margin: '0',
  color: theme.palette.grey['600'],
  marginRight: theme.spacing(1.5),
  ...(isMobile && {
    marginRight: theme.spacing(0.75)
  })
}));
const Label = styled('span')(({ theme }) => ({
  marginRight: theme.spacing(0, 0.25),
  color: theme.palette.text.primary
}));

const SearchInputText = styled(SearchInput, {
  name: 'SearchInputText'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

interface Props {
  toggleSearch?: () => void;
  room?: RoomItemShape;
  searching?: boolean;
}

interface FilterOptionProps {
  id: string;
  checked?: boolean;
  icon: string;
  label: string;
  value: string;
  roomId: string;
  onClick: (value: string) => void;
}

export function SearchFilterOption({
  id,
  checked,
  value,
  label,
  roomId,
  onClick
}: FilterOptionProps) {
  const { dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({ type: value, payload: { identity: roomId } });
    typeof onClick === 'function' && onClick(!checked ? id : undefined);
  };

  return (
    <OptionLabel>
      <input type="checkbox" onClick={handleClick} checked={checked} />
      <Label>{label}</Label>
    </OptionLabel>
  );
}

function SearchMessages({ toggleSearch, room, searching }: Props) {
  const { i18n, dispatch, useIsMobile } = useGlobal();
  const isMobile = useIsMobile(true);

  const inputRef = React.useRef<string>();

  React.useEffect(() => {
    inputRef.current = '';
    debounced();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searching]);

  const handleChange: React.ChangeEventHandler<HTMLInputElement> = e => {
    inputRef.current = e.target.value;
    debounced();
  };

  const handleFilter = React.useCallback(() => {
    dispatch({
      type: 'chat/room/searchMessages',
      payload: {
        roomId: room?.id,
        text: inputRef.current
      },
      meta: {}
    });
  }, [dispatch, room]);

  const debounced = React.useMemo(() => {
    return debounce(handleFilter, 200);
  }, [handleFilter]);

  if (isMobile) {
    return (
      <SearchContainer>
        <SearchInputText
          isMobile={isMobile}
          startAdornment={
            <IconSearch icon={'ico-search-o'} isMobile={isMobile} />
          }
          endAdornment={
            <IconSearch
              icon={'ico-close'}
              onClick={toggleSearch}
              isMobile={isMobile}
            />
          }
          placeholder={i18n.formatMessage({
            id: 'search_messages_dots'
          })}
          autoComplete="off"
          inputProps={{
            'aria-label': 'search',
            'data-testid': 'searchBox',
            autoComplete: 'off',
            autoCapitalize: 'off'
          }}
          inputRef={inputRef}
          onChange={handleChange}
        />
      </SearchContainer>
    );
  }

  return (
    <SearchContainer>
      <SearchInputText
        startAdornment={<IconSearch icon={'ico-search-o'} />}
        endAdornment={<IconSearch icon={'ico-close'} onClick={toggleSearch} />}
        placeholder={i18n.formatMessage({
          id: 'search_messages_dots'
        })}
        autoComplete="off"
        inputProps={{
          'aria-label': 'search',
          'data-testid': 'searchBox',
          autoComplete: 'off',
          autoCapitalize: 'off'
        }}
        inputRef={inputRef}
        onChange={handleChange}
      />
    </SearchContainer>
  );
}

export default SearchMessages;
