import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, InputBase, styled } from '@mui/material';
import { debounce } from 'lodash';
import React, { memo, useCallback, useEffect, useMemo, useRef } from 'react';
// import useStyles from './SearchFilter.styles';

const name = 'ChatSearchFilter';

const OptionLabel = styled('label', {
  name,
  slot: 'OptionLabel',
  shouldForwardProp: prop => prop !== 'optionChecked'
})<{ optionChecked: boolean }>(({ theme, optionChecked }) => ({
  width: '32px',
  height: '32px',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  margin: '0',
  color: '#a2a2a2',
  ...(optionChecked && {
    color: theme.palette.primary.main
  })
}));

const OptionInput = styled('input', { name, slot: 'OptionInput' })(
  ({ theme }) => ({
    display: 'none'
  })
);

const OptionIcon = styled(LineIcon, { name, slot: 'OptionIcon' })(
  ({ theme }) => ({
    display: 'inline-flex',
    alignItems: 'center',
    marginTop: '1px',
    height: '14px',
    marginRight: '4px'
  })
);

const FilterOptionStyled = styled('div')(({ theme }) => ({
  display: 'inline-flex'
}));

const InputBaseStyled = styled(InputBase)(({ theme }) => ({
  display: 'flex',
  width: '100%',
  '&.Mui-focused': {
    borderColor: theme.palette.primary.main
  }
}));

const SearchWrapper = styled(Box, {
  name,
  slot: 'SearchWrapper'
})(({ theme }) => ({
  margin: theme.spacing(0, 2, 1, 2),
  height: theme.spacing(5),
  borderRadius: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center',
  backgroundColor: theme.palette.action.hover,
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

export interface FilterOption {
  checked?: boolean;
  icon: string;
  value: string;
  id: string;
  label: string;
}
interface Props {
  roomId: string;
  placeholder: string;
  hide?: boolean;
  options?: FilterOption[];
  searching?: boolean;
}

interface FilterOptionProps {
  id: string;
  checked?: boolean;
  icon: string;
  value: string;
  roomId: string;
  onClick: (value: string) => void;
}

export function SearchFilterOption({
  id,
  checked,
  icon,
  value,
  roomId,
  onClick
}: FilterOptionProps) {
  const { dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({ type: value, payload: { identity: roomId } });
    typeof onClick === 'function' && onClick(!checked ? id : undefined);
  };

  return (
    <OptionLabel optionChecked={checked}>
      <OptionInput type="checkbox" />
      <OptionIcon icon={icon} onClick={handleClick} />
    </OptionLabel>
  );
}

const Filter = ({
  placeholder,
  hide,
  options = [],
  roomId,
  searching = false
}: Props) => {
  const { dispatch } = useGlobal();
  const text = useRef<string>(null);
  const filter = useRef<string>(null);

  useEffect(() => {
    text.current = '';
    filter.current = null;
    debounced();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searching]);

  const handleChange: React.ChangeEventHandler<HTMLInputElement> = e => {
    text.current = e.target.value;
    debounced();
  };

  const handleClickFilter = (value: string) => {
    filter.current = value;
    debounced();
  };

  const handleFilter = useCallback(() => {
    dispatch({
      type: 'chat/room/searchMessages',
      payload: { roomId, text: text.current }
    });
  }, [dispatch, roomId]);

  const debounced = useMemo(() => {
    return debounce(handleFilter, 200);
  }, [handleFilter]);

  useEffect(() => {
    if (!options.length) return;

    filter.current = options.find(item => item.checked)?.id;
  }, [options]);

  useEffect(() => {
    if (hide || !roomId) return;

    dispatch({
      type: 'chat/room/clearMsgSearch',
      payload: { rid: roomId }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hide, roomId]);

  if (hide) return null;

  return (
    <Box>
      <SearchWrapper>
        <InputBaseStyled
          variant="search"
          onChange={handleChange}
          placeholder={placeholder}
          startAdornment={<LineIcon icon="ico-search-o" />}
          aria-controls="search_complete"
          endAdornment={
            <Box>
              {options && options.length ? (
                <FilterOptionStyled>
                  {options.map(option => (
                    <SearchFilterOption
                      id={option.id}
                      key={option.id.toString()}
                      checked={option?.checked}
                      icon={option.icon}
                      value={option.value}
                      roomId={roomId}
                      onClick={handleClickFilter}
                    />
                  ))}
                </FilterOptionStyled>
              ) : null}
            </Box>
          }
        />
      </SearchWrapper>
    </Box>
  );
};

export default memo(Filter);
