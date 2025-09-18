import { AddFilter } from '@metafox/chat/components/DockPanel';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { ClickOutsideListener, UserAvatar } from '@metafox/ui';
import { styled } from '@mui/material';
import { debounce, isArray } from 'lodash';
import React from 'react';

const name = 'ChatRoomPanel';

const Root = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  borderLeft: theme.mixins.border('secondary'),
  backgroundColor: theme.palette.background.paper,
  width: '100%',
  height: '100%'
}));

const MainContent = styled('div', { name, slot: 'mainContent' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    borderBottom: theme.mixins.border('secondary')
  })
);

const Body = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  backgroundColor: theme.palette.background.paper,
  flex: 1
}));

const Main = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  width: '100%',
  minWidth: 0
}));
const Title = styled('div', { name, slot: 'Title' })(({ theme }) => ({
  fontSize: theme.spacing(1.875),
  padding: theme.spacing(3.5, 2),
  fontWeight: theme.typography.fontWeightMedium,
  alignSelf: 'flex-start',
  color: theme.palette.text.secondary,
  minWidth: '130px'
}));
const SearchWrapper = styled('div', { name, slot: 'SearchWrapper' })(
  ({ theme }) => ({
    flex: 1,
    minWidth: 0,
    margin: theme.spacing(1),
    position: 'relative',
    zIndex: theme.zIndex.speedDial
  })
);

const WrapperSuggest = styled('div', {
  name,
  slot: 'WrapperSuggest',
  shouldForwardProp: props => props !== 'suggestList'
})<{ suggestList?: boolean }>(({ theme, suggestList }) => ({
  ...(suggestList === true && {
    position: 'absolute',
    width: '100%',
    border: theme.mixins.border('secondary'),
    borderTop: 'none',
    backgroundColor: theme.palette.background.paper,
    borderEndEndRadius: theme.spacing(1),
    borderEndStartRadius: theme.spacing(1)
  })
}));

const ItemSuggest = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.75, 2),
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  borderBottom: theme.mixins.border('secondary'),
  '&:last-child': {
    borderBottom: 'none'
  }
}));
const ItemSuggestName = styled('span')(({ theme }) => ({
  ...theme.typography.h5,
  marginLeft: theme.spacing(1)
}));

function NoConversation() {
  const { i18n, dispatch, apiClient } = useGlobal();

  const searchText = React.useRef<string>('');

  const [valueSearch, setValueSearch] = React.useState<string>('');
  const [openSuggest, setOpenSuggest] = React.useState<boolean>(false);
  const [results, setResults] = React.useState([]);

  const debouncedSuggestion = React.useMemo(() => {
    const getSuggestions = () => {
      apiClient
        .get('/friend', {
          params: { limit: 10, q: searchText.current || undefined }
        })
        .then(result => result.data.data)
        .then(items => isArray(items) && setResults(items))
        .catch(err => {});
    };

    return debounce(getSuggestions, 200);
  }, [apiClient, searchText.current]);

  React.useEffect(() => {
    debouncedSuggestion();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleSelectUser = React.useCallback(user => {
    dispatch({
      type: 'chat/newChatRoom',
      payload: { user, isPage: true }
    });
  }, []);

  const onKeyUp = React.useCallback((evt: any) => {}, []);

  const handleChangeSearch = React.useCallback((evt: any) => {
    const query = evt.currentTarget?.value;

    searchText.current = query;
    setValueSearch(query);
    debouncedSuggestion();

    setOpenSuggest(true);
  }, []);

  const handleClick = () => {
    setOpenSuggest(false);
  };

  return (
    <Root>
      <MainContent>
        <Title>{i18n.formatMessage({ id: 'new_message' })}</Title>

        <ClickOutsideListener onClickAway={handleClick}>
          <SearchWrapper>
            <AddFilter
              sx={{ p: 0 }}
              valueSearch={valueSearch}
              onChangeSearch={handleChangeSearch}
              onKeyUp={onKeyUp}
              placeholder={i18n.formatMessage({
                id: 'type_name_start_new_conversation'
              })}
              size={30}
              isShowButtonSubmit={false}
            />
            {openSuggest ? (
              <WrapperSuggest suggestList={Boolean(results && results.length)}>
                <div>
                  <ScrollContainer autoHide autoHeight>
                    {results.map((user, idx) => (
                      <React.Fragment key={idx}>
                        <ItemSuggest onClick={() => handleSelectUser(user)}>
                          <UserAvatar user={user} size={32} noLink />
                          <ItemSuggestName>{user.full_name}</ItemSuggestName>
                        </ItemSuggest>
                      </React.Fragment>
                    ))}
                  </ScrollContainer>
                </div>
              </WrapperSuggest>
            ) : null}
          </SearchWrapper>
        </ClickOutsideListener>
      </MainContent>
      <Body>
        <Main></Main>
      </Body>
    </Root>
  );
}

export default NoConversation;
