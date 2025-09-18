import {
  SEARCH_DOWN,
  SEARCH_UP,
  THROTTLE_SEARCH
} from '@metafox/chatplus/constants';
import { useChatRoom } from '@metafox/chatplus/hooks';
import { RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  useMediaQuery,
  Checkbox,
  InputBase,
  styled,
  Typography
} from '@mui/material';
import { debounce, isEmpty } from 'lodash';
import React, { useMemo, useRef } from 'react';
import { FilterOption } from '../../DockPanel/SearchFilter';

const SearchContainer = styled('div')(({ theme }) => ({
  width: '100%',
  transition: 'all .2s ease'
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
  '& .MuiInputBase-input': {
    marginRight: theme.spacing(1),
    ...(!isMobile && {
      minWidth: '200px'
    })
  },
  [theme.breakpoints.down('lg')]: {
    width: '100%',
    maxWidth: '100%',
    margin: 0,
    padding: theme.spacing(0, 1),
    '& input': {
      paddingTop: theme.spacing(0.5)
    }
  },
  ...(isMobile && {
    width: '100%',
    maxWidth: '100%',
    margin: 0,
    padding: theme.spacing(0, 1),
    '& input': {
      paddingTop: theme.spacing(0.5)
    }
  })
}));
const IconSearch = styled(LineIcon, {
  shouldForwardProp: props => props !== 'isMobile' && props !== 'disabled'
})<{ isMobile?: boolean; disabled?: boolean }>(
  ({ theme, isMobile, disabled }) => ({
    color: theme.palette.text.primary,
    transition: 'all .2s ease',
    cursor: 'pointer',
    padding: theme.spacing(0, 1),
    ...(isMobile && {
      padding: 0
    }),
    ...(disabled && {
      cursor: 'not-allowed',
      color: theme.palette.text.disabled
    })
  })
);

const FilterOptions = styled('div')(({ theme }) => ({
  display: 'inline-flex'
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

interface Props {
  toggleSearch?: () => void;
  room?: RoomItemShape;
  messageFilter?: any;
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
  icon,
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
    <OptionLabel onClick={handleClick}>
      <Checkbox checked={checked} size="small" />
      <Label>{label}</Label>
    </OptionLabel>
  );
}

function SearchMessages({
  toggleSearch,
  room,
  messageFilter,
  searching
}: Props) {
  const { i18n, dispatch, useIsMobile, useTheme } = useGlobal();
  const isMobile = useIsMobile(true);
  const theme = useTheme();

  const isIpad = useMediaQuery(theme.breakpoints.down('lg'));

  const chatRoom = useChatRoom(room?.id);

  const { msgSearch } = chatRoom || {};

  const indexMessage = msgSearch ? msgSearch?.slot + 1 : 0;

  const [totalMessagesFilter, setTotalMessagesFilter] = React.useState(
    msgSearch?.total || 0
  );
  const inputRef = React.useRef<string>();
  const ref = React.useRef<any>();
  const filter = useRef<string>(null);

  const placeholder = React.useMemo(() => {
    if (chatRoom?.pinned) {
      return i18n.formatMessage({ id: 'search_pinned_messages' });
    }

    if (chatRoom?.starred) {
      return i18n.formatMessage({ id: 'search_starred_messages' });
    }

    return i18n.formatMessage({ id: 'search_messages_dots' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [chatRoom?.pinned, chatRoom?.starred]);

  const filterOptions: FilterOption[] = useMemo(() => {
    return [
      {
        icon: 'ico-thumb-tack',
        label: i18n.formatMessage({ id: 'pinned' }),
        checked: chatRoom?.pinned,
        value: 'chatplus/room/pinnedMessages',
        id: 'pin'
      },
      {
        icon: 'ico-star-o',
        label: i18n.formatMessage({ id: 'starred' }),
        checked: chatRoom?.starred,
        value: 'chatplus/room/starredMessages',
        id: 'star'
      }
    ];
  }, [i18n, chatRoom]);

  const [hasOptionFilter, setHasFilterOptions] = React.useState(true);

  const disableSearchUp = useMemo(() => {
    if (isEmpty(msgSearch)) return true;

    if (msgSearch?.slot === 0) return true;

    return false;
  }, [msgSearch]);

  const disableSearchDown = useMemo(() => {
    if (isEmpty(msgSearch)) return true;

    if (totalMessagesFilter === indexMessage) return true;

    return false;
  }, [msgSearch, totalMessagesFilter, indexMessage]);

  const focusInput = () => {
    if (isMobile || isIpad) {
      setTimeout(() => {
        setHasFilterOptions(false);
      }, 200);
    }
  };

  const blurInput = () => {
    if (isMobile || isIpad) {
      setHasFilterOptions(true);
    }
  };

  React.useEffect(() => {
    setHasFilterOptions(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [chatRoom?.pinned, chatRoom?.starred]);

  React.useEffect(() => {
    if (isEmpty(msgSearch)) {
      setTotalMessagesFilter(0);
    }
  }, [msgSearch]);

  React.useEffect(() => {
    inputRef.current = '';
    filter.current = null;
    // debounced();
  }, [searching]);

  const handleClickFilter = (value: string) => {
    filter.current = value;
    debounced();
  };

  const handleSuccess = result => {
    setTotalMessagesFilter(result?.total || 0);
  };

  const handleFailure = () => {};

  const handleChange: React.ChangeEventHandler<HTMLInputElement> = e => {
    inputRef.current = e.target.value;
    debounced();
  };

  const handleFilter = React.useCallback(() => {
    if (isMobile) {
      dispatch({
        type: filter.current
          ? 'chatplus/room/pinnedMessages'
          : 'chatplus/room/searchMessages',
        payload: {
          roomId: room?.id,
          text: inputRef.current,
          filter: filter.current,
          isBuddy: true
        },
        meta: {
          onSuccess: handleSuccess,
          onFailure: handleFailure
        }
      });

      return;
    }

    dispatch({
      type: 'chatplus/room/searchMessages',
      payload: {
        roomId: room?.id,
        text: inputRef.current,
        filter: filter.current
      },
      meta: {
        onSuccess: handleSuccess,
        onFailure: handleFailure
      }
    });
  }, [dispatch, room, isMobile]);

  const handleSearchUp = React.useCallback(() => {
    if (disableSearchUp) return;

    dispatch({
      type: 'chatplus/room/searchChangePosition',
      payload: {
        rid: room?.id,
        mid: msgSearch?.id,
        type: SEARCH_UP
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [msgSearch?.id, disableSearchUp]);

  const handleSearchDown = React.useCallback(() => {
    if (disableSearchDown) return;

    dispatch({
      type: 'chatplus/room/searchChangePosition',
      payload: {
        rid: room?.id,
        mid: msgSearch?.id,
        type: SEARCH_DOWN
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [msgSearch?.id, disableSearchDown]);

  const debounced = React.useMemo(() => {
    return debounce(handleFilter, THROTTLE_SEARCH);
  }, [handleFilter]);

  if (isMobile) {
    return (
      <SearchContainer>
        <SearchInput
          isMobile={isMobile}
          endAdornment={
            <>
              <>
                <Typography justifyContent={'flex-end'} display={'inline-flex'}>
                  {indexMessage}/{totalMessagesFilter}
                </Typography>
                <IconSearch
                  icon={'ico-angle-up'}
                  onClick={handleSearchUp}
                  disabled={disableSearchUp}
                />
                <IconSearch
                  icon={'ico-angle-down'}
                  onClick={handleSearchDown}
                  disabled={disableSearchDown}
                />
              </>
              <IconSearch icon={'ico-close'} onClick={toggleSearch} />
            </>
          }
          placeholder={placeholder}
          autoComplete="off"
          inputProps={{
            'aria-label': 'search',
            'data-testid': 'searchBox',
            autoComplete: 'off',
            autoCapitalize: 'off',
            onFocus: focusInput,
            onBlur: blurInput
          }}
          ref={ref}
          inputRef={inputRef}
          onChange={handleChange}
        />
      </SearchContainer>
    );
  }

  return (
    <SearchContainer>
      <SearchInput
        startAdornment={
          <>
            {hasOptionFilter ? (
              <FilterOptions>
                {filterOptions.map(option => (
                  <SearchFilterOption
                    id={option.id}
                    key={option.id.toString()}
                    checked={option.checked}
                    icon={option.icon}
                    value={option.value}
                    label={option.label}
                    roomId={room?.id}
                    onClick={handleClickFilter}
                  />
                ))}
              </FilterOptions>
            ) : null}
            <IconSearch icon={'ico-search-o'} />
          </>
        }
        endAdornment={
          <>
            <Typography justifyContent={'flex-end'} display={'inline-flex'}>
              {indexMessage}/{totalMessagesFilter}
            </Typography>
            <IconSearch
              icon={'ico-angle-up'}
              onClick={handleSearchUp}
              disabled={disableSearchUp}
            />
            <IconSearch
              icon={'ico-angle-down'}
              onClick={handleSearchDown}
              disabled={disableSearchDown}
            />
            <IconSearch icon={'ico-close'} onClick={toggleSearch} />
          </>
        }
        placeholder={placeholder}
        autoComplete="off"
        inputProps={{
          'aria-label': 'search',
          'data-testid': 'searchBox',
          autoComplete: 'off',
          autoCapitalize: 'off',
          onFocus: focusInput,
          onBlur: blurInput
        }}
        ref={ref}
        inputRef={inputRef}
        onChange={handleChange}
      />
    </SearchContainer>
  );
}

export default SearchMessages;
