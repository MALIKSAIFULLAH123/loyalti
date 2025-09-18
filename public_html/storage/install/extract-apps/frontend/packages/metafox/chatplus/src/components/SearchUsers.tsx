import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import ClearIcon from '@mui/icons-material/Clear';
import { Chip, Divider, styled, Typography, Skeleton } from '@mui/material';
import { differenceBy } from 'lodash';
import { default as React } from 'react';
import { useSelector } from 'react-redux';
import { useChatUserItem, useSessionUser } from '@metafox/chatplus/hooks';
import { getFriends } from '@metafox/chatplus/selectors';
import { SuggestionItemShape } from '@metafox/chatplus/types';
import Avatar from './Avatar';
import { AddFilter } from './DockPanel';
import { useGetSpotlightUserInit } from '../hooks/useSpotlight';
import { ItemMedia, ItemText, ItemTitle } from '@metafox/ui';

const WrapperSuggest = styled('div', {
  shouldForwardProp: props =>
    props !== 'suggestList' && props !== 'height' && props !== 'loading'
})<{ suggestList?: boolean; height?: number; loading?: boolean }>(
  ({ theme, suggestList, height, loading }) => ({
    padding: theme.spacing(1, 1, 1, 2),
    ...(suggestList === false &&
      !loading && {
        display: 'flex',
        justifyContent: 'center'
      }),
    ...(loading && {
      display: 'block'
    }),
    ...(height && {
      height
    })
  })
);
const WrapperChip = styled('div', {
  shouldForwardProp: props => props !== 'users'
})<{ users?: number }>(({ theme, users }) => ({
  ...(users && { padding: theme.spacing(0, 2, 1, 2) }),
  listStyle: 'none',
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap'
}));
const ListItemChip = styled('li')(({ theme }) => ({
  margin: theme.spacing(0.5),
  maxWidth: '100%'
}));
const ItemSuggest = styled('div', {
  shouldForwardProp: props => props !== 'loading'
})<{
  loading?: boolean;
}>(({ theme, loading }) => ({
  padding: theme.spacing(1.75, 0),
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  ...(loading && {
    padding: theme.spacing(1.25, 0)
  })
}));
const ItemSuggestName = styled('span')(({ theme }) => ({
  ...theme.typography.h5,
  marginLeft: theme.spacing(1)
}));
const AddFilterText = styled(AddFilter, {
  name: 'AddFilterText'
})(({ theme }) => ({
  '& input::placeholder, ico': {
    color: theme.palette.text.hint
  }
}));
interface Props {
  variant?: 'direct' | 'group' | string;
  placeholder?: string;
  users: SuggestionItemShape[];
  members?: SuggestionItemShape[];
  setUsers: React.Dispatch<React.SetStateAction<SuggestionItemShape[]>>;
  onSubmit?: () => void;
  divider?: boolean;
  isShowButtonSubmit?: boolean;
  height?: number;
}

const ItemUser = ({ user, onSelectItem }: any) => {
  const userInfo = useChatUserItem(user?._id);

  return (
    <>
      <ItemSuggest onClick={() => onSelectItem(user)}>
        <Avatar
          name={user.label}
          username={user.value}
          avatarETag={user?.avatarETag || userInfo?.avatarETag}
          size={32}
        />
        <ItemSuggestName>{user.label}</ItemSuggestName>
      </ItemSuggest>
      <Divider />
    </>
  );
};

const ContentView = ({ loading, suggest, height, onSelectItem }: any) => {
  const { i18n } = useGlobal();

  if (loading) {
    return (
      <>
        {Array(4)
          .fill(0)
          .map((_, index) => (
            <ItemSuggest key={index} loading>
              <ItemMedia>
                <Skeleton
                  variant="avatar"
                  width={40}
                  height={40}
                  sx={{ mr: 2 }}
                />
              </ItemMedia>
              <ItemText>
                <ItemTitle>
                  <Skeleton variant="text" width="50%" />
                </ItemTitle>
              </ItemText>
            </ItemSuggest>
          ))}
      </>
    );
  }

  if (suggest && suggest.length) {
    return (
      <ScrollContainer autoHide autoHeight={height ? false : true}>
        {suggest.map((user, idx) => (
          <ItemUser key={user?._id} user={user} onSelectItem={onSelectItem} />
        ))}
      </ScrollContainer>
    );
  }

  return (
    <Typography mt={1} variant="body1">
      {i18n.formatMessage({ id: 'no_friend_found' })}
    </Typography>
  );
};

function SearchUsers({
  variant = 'direct',
  placeholder,
  users = [],
  members = [],
  divider = true,
  isShowButtonSubmit = true,
  setUsers,
  onSubmit,
  height = 335
}: Props) {
  const { i18n, dispatch } = useGlobal();
  const userSpotlightDefault = useGetSpotlightUserInit();

  const [userSpotlight, setUserSpotlight] = React.useState(
    userSpotlightDefault || []
  );
  const [loading, setLoading] = React.useState(false);

  const userSession = useSessionUser();

  const friends = useSelector(getFriends);

  const [searchText, setValueSearch] = React.useState<string>('');

  React.useEffect(() => {
    return () => {
      dispatch({
        type: 'chatplus/spotlight/reset'
      });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const placeholderSearch =
    placeholder || i18n.formatMessage({ id: 'search_people' });

  const onSelectItem = (user: SuggestionItemShape) => {
    setValueSearch('');
    setUsers(prev => [...prev, user]);
  };

  const onRemoveItem = (user: SuggestionItemShape) => {
    setUsers(prev => prev.filter(x => x.value !== user.value));
  };

  const onKeyUp = React.useCallback(
    (evt: any) => {
      const query = evt.currentTarget?.value;

      setLoading(true);
      dispatch({
        type: 'chatplus/spotlight/user',
        payload: { query },
        meta: {
          onSuccess: values => {
            setUserSpotlight(values || []);
            setLoading(false);
          }
        }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [dispatch, searchText, friends]
  );

  const handleChangeSearch = React.useCallback((evt: any) => {
    const query = evt.currentTarget?.value;
    setValueSearch(query);
  }, []);

  const chipList = React.useMemo(() => {
    const memberValues = members.map(member => member.value);

    return users.filter(user => !memberValues.includes(user.value));
  }, [users, members]);

  const resultsUser = userSpotlight
    .filter(user => {
      if (variant === 'group') return user.username !== userSession?.username;

      if (variant === 'direct') return user;
    })
    .map(x => ({
      value: x.username,
      label: x.name,
      _id: x._id,
      avatarETag: x?.avatarETag
    }));

  const suggest = differenceBy(resultsUser, users, 'value');

  return (
    <div>
      <AddFilterText
        disabled={!users.length}
        valueSearch={searchText}
        onChangeSearch={handleChangeSearch}
        onKeyUp={onKeyUp}
        placeholder={placeholderSearch}
        size={30}
        onSubmit={onSubmit}
        label={i18n.formatMessage({ id: 'next' })}
        variant={variant}
        isShowButtonSubmit={isShowButtonSubmit}
      />
      <WrapperChip users={users.length}>
        {chipList.map((user, idx) => {
          return (
            <ListItemChip key={idx}>
              <Chip
                size="medium"
                onDelete={() => onRemoveItem(user)}
                label={user.label}
                deleteIcon={<ClearIcon style={{ fontSize: '20px' }} />}
              />
            </ListItemChip>
          );
        })}
      </WrapperChip>
      {divider && <Divider />}
      <WrapperSuggest
        suggestList={Boolean(suggest && suggest.length)}
        height={height}
        loading={loading}
      >
        <ContentView
          loading={loading}
          suggest={suggest}
          height={height}
          onSelectItem={onSelectItem}
        />
      </WrapperSuggest>
    </div>
  );
}

export default SearchUsers;
