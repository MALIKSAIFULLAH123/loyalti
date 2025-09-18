import {
  THROTTLE_SEARCH
} from '@metafox/chatplus/constants';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { InputBase, styled, Box } from '@mui/material';
import { debounce } from 'lodash';
import React, { memo, useEffect, useMemo, useRef } from 'react';
import { ChatRoomShape } from '@metafox/chatplus/types';

const Root = styled('div')(({ theme }) => ({
  width: '100%'
}));

const IconSearchWrapper = styled('div')(({ theme }) => ({
  display: 'inline-flex',
  padding: theme.spacing(0, 1)
}));

const SearchWrapper = styled(Box, {
  name: 'SearchBox',
  slot: 'Root'
})(({ theme }) => ({
  width: '100%',
  height: theme.spacing(5),
  borderRadius: theme.spacing(2.5),
  display: 'flex',
  alignItems: 'center',
  backgroundColor: theme.palette.action.hover,
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

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

export function CloseSearch({ roomId }) {
  const { dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({
      type: 'chatplus/room/toggleSearching',
      payload: { identity: roomId }
    });
  };

  return <LineIcon icon={'ico-close'} onClick={handleClick} />;
}

const Filter = ({
  hide,
  roomId,
  searching,
  chatRoom,
  isBuddy = false
}: Props) => {
  const { dispatch, i18n } = useGlobal();
  const text = useRef<string>(null);
  const filter = useRef<string>(null);

  const [textInput, setTextInput] = React.useState(null);
  let placeholder = i18n.formatMessage({ id: 'search_messages_dots' });

  if (chatRoom?.pinned) {
    placeholder = i18n.formatMessage({ id: 'search_pinned_messages' });
  }

  if (chatRoom?.starred) {
    placeholder = i18n.formatMessage({ id: 'search_starred_messages' });
  }

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
          fullWidth
          endAdornment={
            <WrapActionSearch>
                <IconSearchWrapper>
                  <CloseSearch roomId={roomId} />
                </IconSearchWrapper>
            </WrapActionSearch>
          }
        />
      </SearchWrapper>
    </Root>
  );
};

export default memo(Filter);
