import { AddFilter } from '@metafox/chat/components/DockPanel';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { UserAvatar } from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { Divider, Skeleton, styled } from '@mui/material';
import { debounce, isArray } from 'lodash';
import { default as React } from 'react';

const WrapperSuggest = styled('div')(({ theme }) => ({
  padding: theme.spacing(1, 1, 1, 2)
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
  users?: UserItemShape[];
  setUsers?: React.Dispatch<React.SetStateAction<UserItemShape[]>>;
  onSubmit?: () => void;
  divider?: boolean;
  isShowButtonSubmit?: boolean;
}

const LoadingSkeleton = () => {
  return (
    <>
      {Array.from({ length: 4 }, (_, idx) => (
        <React.Fragment key={idx}>
          <ItemSuggest>
            <Skeleton variant="avatar" width={35} height={32} />
            <Skeleton variant="text" width={'100%'} sx={{ ml: 1 }} />
          </ItemSuggest>
          <Divider />
        </React.Fragment>
      ))}
    </>
  );
};

function SearchUsers({
  variant = 'direct',
  placeholder,
  divider = true,
  isShowButtonSubmit = false,
  onSubmit
}: Props) {
  const { i18n, dispatch, apiClient } = useGlobal();

  placeholder = placeholder || i18n.formatMessage({ id: 'search_people' });

  const searchText = React.useRef<string>('');

  const [valueSearch, setValueSearch] = React.useState<string>('');
  const [results, setResults] = React.useState([]);
  const [loading, setLoading] = React.useState(false);

  const debouncedSuggestion = React.useMemo(() => {
    setLoading(true);
    const getSuggestions = () => {
      apiClient
        .get('/friend', {
          params: { limit: 10, q: searchText.current || undefined }
        })
        .then(result => result.data.data)
        .then(items => {
          isArray(items) && setResults(items);
          setLoading(false);
        })
        .catch(err => {
          setLoading(false);
        });
    };

    return debounce(getSuggestions, 200);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [apiClient, searchText.current]);

  React.useEffect(() => {
    debouncedSuggestion();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSelectUser = React.useCallback(user => {
    dispatch({
      type: 'chat/newChatRoom',
      payload: { user, isPage: false }
    });
  }, []);

  const onKeyUp = () => {};

  const handleChangeSearch = React.useCallback((evt: any) => {
    const query = evt.currentTarget?.value;

    searchText.current = query;
    setValueSearch(query);
    debouncedSuggestion();
  }, []);

  return (
    <div>
      <AddFilterText
        valueSearch={valueSearch}
        onChangeSearch={handleChangeSearch}
        onKeyUp={onKeyUp}
        placeholder={placeholder}
        size={30}
        variant={variant}
        isShowButtonSubmit={isShowButtonSubmit}
      />
      {divider && <Divider />}
      <WrapperSuggest>
        {loading ? (
          <LoadingSkeleton />
        ) : (
          <ScrollContainer
            autoHide
            autoHeight={false}
            autoHeightMax={320}
            style={{ height: 320 }}
          >
            {results.map((user, idx) => (
              <div key={idx}>
                <ItemSuggest onClick={() => handleSelectUser(user)}>
                  <UserAvatar user={user} size={32} noLink />
                  <ItemSuggestName>{user.full_name}</ItemSuggestName>
                </ItemSuggest>
                <Divider />
              </div>
            ))}
          </ScrollContainer>
        )}
      </WrapperSuggest>
    </div>
  );
}

export default SearchUsers;
