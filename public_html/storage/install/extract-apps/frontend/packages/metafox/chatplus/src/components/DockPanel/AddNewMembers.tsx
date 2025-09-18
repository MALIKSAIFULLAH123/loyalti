import { useChatRoom, useSessionUser } from '@metafox/chatplus/hooks';
import { RoomType, SuggestionItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { Button, Chip, Divider, styled } from '@mui/material';
import { differenceBy } from 'lodash';
import React, { memo, useEffect } from 'react';
import Avatar from '../Avatar';

const name = 'AddNewMembers';

const WrapperSuggest = styled('div')(({ theme }) => ({
  padding: theme.spacing(0, 1, 0, 2)
}));
const WrapperChip = styled('div', {
  shouldForwardProp: props => props !== 'users'
})<{ users?: number }>(({ theme, users }) => ({
  ...(users && { padding: theme.spacing(0, 1, 1, 2) }),
  listStyle: 'none',
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap'
}));
const ListItemChip = styled('li')(({ theme }) => ({
  margin: theme.spacing(0.5)
}));
const ItemSuggest = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.75, 0),
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer'
}));
const ItemSuggestName = styled('span')(({ theme }) => ({
  ...theme.typography.h5,
  marginLeft: theme.spacing(1)
}));

const RootSearch = styled('div', { name, slot: 'RootSearch' })(
  ({ theme }) => ({})
);
const SearchControl = styled('div', { name, slot: 'SearchControl' })(
  ({ theme }) => ({
    padding: theme.spacing(0, 1, 0, 2),
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center'
  })
);

const ButtonCustom = styled(Button, { name, slot: 'ButtonCustom' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(0.5)
  })
);

const InputWrapper = styled('input', { name, slot: 'InputWrapper' })(
  ({ theme }) => ({
    // padding: theme.spacing(0, 1, 0, 2),
    zIndex: 'auto',
    backgroundColor: theme.mixins.backgroundColor('paper'),
    color:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['50']
        : theme.palette.grey['700'],
    height: '35px',
    borderRadius: '2px',
    fontSize: '14px',
    padding: '8px',
    paddingLeft: '12px',
    paddingRight: '72px',
    display: 'block',
    width: '100%',
    margin: '0',
    border: theme.mixins.border('secondary'),
    '&:focus': {
      borderColor: '#a2a2a2',
      boxShadow: '0 1px 4px 0 rgba(0,0,0,0.1)',
      outline: 'none'
    },
    '&::placeholder': {
      fontSize: '14px'
    }
  })
);
interface Props {
  roomId?: string;
  hide?: boolean;
}

function AddNewMembers({ hide, roomId }: Props) {
  const { i18n, dispatch, chatplus } = useGlobal();

  const { resultSearchMembers, searchText } = useChatRoom(roomId) || {};

  const [users, setUsers] = React.useState<SuggestionItemShape[]>([]);
  const [memberUsers, setMemberUsers] = React.useState<any[]>([]);
  const [valueSearch, setValueSearch] = React.useState<string>('');
  const userSession = useSessionUser();

  useEffect(() => {
    setUsers([]);

    chatplus
      .getRoomMembers(roomId, null)
      .then(data => {
        if (data.users) {
          const tmp = data.users.map(u => ({
            value: u.username,
            label: u.name
          }));
          setMemberUsers(tmp);
        }
      })
      .catch(err => {
        // console.log(err);
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hide, roomId]);

  const onSelectItem = (user: SuggestionItemShape) => {
    setUsers(prev => [...prev, user]);
    setValueSearch('');
    dispatch({
      type: 'chatplus/room/clearSearchUser',
      payload: { rid: roomId }
    });
  };

  const onRemoveItem = (user: SuggestionItemShape) => {
    setUsers(prev => prev.filter(x => x.value !== user.value));
  };

  const handleKeyUp = React.useCallback(
    (evt: any) => {
      const query = evt.currentTarget?.value;

      if (query !== searchText) {
        dispatch({
          type: 'chatplus/room/searchUser',
          payload: { query, rid: roomId }
        });
      }

      // track key code for autocomplete
      // move down, move up, move left, move right
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [dispatch, searchText]
  );
  const handleChangeSearch = React.useCallback((evt: any) => {
    const query = evt.currentTarget?.value;
    setValueSearch(query);
  }, []);

  const handleSubmit = React.useCallback(() => {
    const memberCount = users.length;

    if (memberCount) {
      const userPayload = users.map(u => u.value);

      dispatch({
        type: 'chatplus/room/addUsers',
        payload: { rid: roomId, users: userPayload }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, users]);

  if (hide) return null;

  const resultsUser = resultSearchMembers
    .filter(user => user.username !== userSession.username)
    .map(x => ({
      value: x.username,
      label: x.name
    }));

  const userFilterMember = differenceBy(resultsUser, memberUsers, 'value');
  const suggest = differenceBy(userFilterMember, users, 'value');

  return (
    <div>
      <RootSearch>
        <SearchControl>
          <InputWrapper
            type="text"
            placeholder={i18n.formatMessage({ id: 'add_members_dot' })}
            value={valueSearch}
            onKeyUp={handleKeyUp}
            onChange={handleChangeSearch}
          />
          <ButtonCustom
            disabled={!users.length}
            size="medium"
            color="primary"
            variant="text"
            onClick={handleSubmit}
          >
            {i18n.formatMessage({ id: 'done' })}
          </ButtonCustom>
        </SearchControl>
      </RootSearch>
      <WrapperChip users={users.length}>
        {users.map((user, idx) => (
          <ListItemChip key={idx}>
            <Chip
              size="medium"
              onDelete={() => onRemoveItem(user)}
              label={user.label}
            />
          </ListItemChip>
        ))}
      </WrapperChip>
      <WrapperSuggest>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={users.length ? 268 : 316}
          autoHeightMin={users.length ? 268 : 316}
        >
          {suggest.map((user, idx) => (
            <React.Fragment key={idx}>
              <ItemSuggest onClick={() => onSelectItem(user)}>
                <Avatar
                  name={user.label}
                  username={user.value}
                  size={32}
                  roomType={RoomType.Direct}
                  avatarETag={Date.now.toString()}
                />
                <ItemSuggestName>{user.label}</ItemSuggestName>
              </ItemSuggest>
              <Divider />
            </React.Fragment>
          ))}
        </ScrollContainer>
      </WrapperSuggest>
    </div>
  );
}
export default memo(AddNewMembers);
