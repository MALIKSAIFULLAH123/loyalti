import {
  SEARCH_DOWN,
  SEARCH_UP,
  THROTTLE_SEARCH
} from '@metafox/chatplus/constants';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { InputBase, styled, Box, Tooltip } from '@mui/material';
import clsx from 'clsx';
import { debounce, isEmpty } from 'lodash';
import React, { memo, useEffect, useMemo, useRef } from 'react';
import useStyles from './SearchFilter.styles';
import { ChatRoomShape } from '@metafox/chatplus/types';

const Root = styled('div')(({ theme }) => ({}));

const FilterOptionStyled = styled('div')(({ theme }) => ({
  display: 'inline-flex'
}));

const StyleItemOption = styled('label', {
  shouldForwardProp: props => props !== 'id'
})<{ id?: string }>(({ theme, id }) => ({
  cursor: 'pointer',
  ...(id === 'close' && {
    '& span.ico': {
      '&:hover': {
        background: theme.palette.action.selected,
        width: theme.spacing(2.8),
        height: theme.spacing(2.8),
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        borderRadius: '40px'
      }
    }
  })
}));

const SearchWrapper = styled(Box, {
  name: 'SearchBox',
  slot: 'Root'
})(({ theme }) => ({
  margin: theme.spacing(0, 2, 1, 2),
  height: theme.spacing(5),
  borderRadius: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center',
  backgroundColor: theme.palette.action.hover,
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const IconSearch = styled(LineIcon, {
  shouldForwardProp: props => props !== 'isMobile' && props !== 'disabled'
})<{ isMobile?: boolean; disabled?: boolean }>(
  ({ theme, isMobile, disabled }) => ({
    color: theme.palette.text.primary,
    transition: 'all .2s ease',
    cursor: 'pointer',
    padding: theme.spacing(0, 1),
    width: theme.spacing(4),
    height: theme.spacing(4),
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    fontWeight: theme.typography.fontWeightSemiBold,
    ...(isMobile && {
      padding: 0
    }),
    ...(disabled && {
      cursor: 'not-allowed',
      color: `${theme.palette.text.disabled} !important`,
      fontWeight: theme.typography.fontWeightMedium
    })
  })
);

const WrapActionSearch = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

export interface FilterOption {
  checked?: boolean;
  icon: string;
  value: string;
  id: string;
  label?: string;
  tooltip?: string;
}
interface Props {
  roomId: string;
  placeholder?: string;
  size?: number;
  hide?: boolean;
  options?: FilterOption[];
  searching?: boolean;
  chatRoom?: ChatRoomShape;
  isBuddy?: boolean;
}

type ClassKey = 'optionLabel' | 'optionChecked' | 'optionInput' | 'optionIcon';

interface FilterOptionProps {
  id: string;
  checked?: boolean;
  icon: string;
  classes: Record<ClassKey, string>;
  value: string;
  roomId: string;
  onClick: (value: string) => void;
  tooltip?: string;
}

export function SearchFilterOption({
  id,
  checked,
  icon,
  classes,
  value,
  roomId,
  onClick,
  tooltip
}: FilterOptionProps) {
  const { dispatch, i18n } = useGlobal();

  const handleClick = () => {
    dispatch({ type: value, payload: { identity: roomId } });
    typeof onClick === 'function' && onClick(!checked ? id : undefined);
  };

  return (
    <Tooltip
      title={tooltip ? i18n.formatMessage({ id: tooltip }) : ''}
      placement="top"
    >
      <StyleItemOption
        id={id}
        className={clsx(classes.optionLabel, checked && classes.optionChecked)}
      >
        <input className={classes.optionInput} type="checkbox" />
        <LineIcon
          icon={icon}
          className={classes.optionIcon}
          onClick={handleClick}
        />
      </StyleItemOption>
    </Tooltip>
  );
}

const Filter = ({
  hide,
  roomId,
  searching,
  chatRoom,
  isBuddy = false
}: Props) => {
  const classes = useStyles();
  const { dispatch, i18n } = useGlobal();
  const text = useRef<string>(null);
  const filter = useRef<string>(null);

  const { msgSearch } = chatRoom || {};

  const indexMessage = msgSearch ? msgSearch?.slot + 1 : 0;

  const [totalMessagesFilter, setTotalMessagesFilter] = React.useState(
    msgSearch?.total || 0
  );

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

  const [textInput, setTextInput] = React.useState(null);
  let placeholder = i18n.formatMessage({ id: 'search_messages_dots' });

  if (chatRoom?.pinned) {
    placeholder = i18n.formatMessage({ id: 'search_pinned_messages' });
  }

  if (chatRoom?.starred) {
    placeholder = i18n.formatMessage({ id: 'search_starred_messages' });
  }

  // I use memo here for avoid rendering SearchFilter Component
  const options: FilterOption[] = useMemo(
    () => [
      {
        icon: 'ico-close',
        value: 'chatplus/room/toggleSearching',
        tooltip: 'close',
        id: 'close'
      }
    ],
    []
  );

  useEffect(() => {
    text.current = '';
    setTextInput('');
    filter.current = null;
    debounced();
  }, [searching]);

  const handleChange: React.ChangeEventHandler<HTMLInputElement> = e => {
    text.current = e.target.value;
    setTextInput(e.target.value);
    debounced();
  };

  const handleSuccess = result => {
    setTotalMessagesFilter(result?.total || 0);
  };

  const handleFailure = () => {};

  const handleFilter = React.useCallback(() => {
    dispatch({
      type: filter.current
        ? 'chatplus/room/pinnedMessages'
        : 'chatplus/room/searchMessages',
      payload: {
        roomId,
        text: text.current,
        filter: filter.current,
        isBuddy
      },
      meta: {
        onSuccess: handleSuccess,
        onFailure: handleFailure
      }
    });
  }, [dispatch, roomId]);

  const debounced = useMemo(() => {
    return debounce(handleFilter, THROTTLE_SEARCH);
  }, [handleFilter]);

  useEffect(() => {
    if (!roomId) return;

    text.current = '';
    setTextInput('');

    if (chatRoom?.pinned) {
      filter.current = 'pin';
      debounced();
    }

    if (chatRoom?.starred) {
      filter.current = 'star';
      debounced();
    }

    if (!chatRoom?.pinned && !chatRoom?.starred) {
      filter.current = null;
      debounced();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [chatRoom?.pinned, chatRoom?.starred, roomId]);

  const handleSearchUp = React.useCallback(() => {
    if (disableSearchUp) return;

    dispatch({
      type: 'chatplus/room/searchChangePosition',
      payload: {
        rid: roomId,
        mid: msgSearch?.id,
        type: SEARCH_UP
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [msgSearch?.id, roomId, disableSearchUp]);

  const handleSearchDown = React.useCallback(() => {
    if (disableSearchDown) return;

    dispatch({
      type: 'chatplus/room/searchChangePosition',
      payload: {
        rid: roomId,
        mid: msgSearch?.id,
        type: SEARCH_DOWN
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [msgSearch?.id, roomId, disableSearchDown]);

  if (hide || !roomId) return null;

  return (
    <Root>
      <SearchWrapper>
        <InputBase
          sx={{ width: '100%' }}
          variant="search"
          onChange={handleChange}
          placeholder={placeholder}
          startAdornment={<LineIcon icon="ico-search-o" />}
          aria-controls="search_complete"
          value={textInput}
          endAdornment={
            <WrapActionSearch>
              {!chatRoom?.pinned && !chatRoom?.starred ? (
                <>
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
              ) : null}
              {options && options.length ? (
                <FilterOptionStyled>
                  {options.map(option => (
                    <SearchFilterOption
                      id={option.id}
                      key={option.id.toString()}
                      checked={option?.checked}
                      classes={classes}
                      icon={option.icon}
                      value={option.value}
                      roomId={roomId}
                      tooltip={option?.tooltip}
                    />
                  ))}
                </FilterOptionStyled>
              ) : null}
            </WrapActionSearch>
          }
        />
      </SearchWrapper>
    </Root>
  );
};

export default memo(Filter);
