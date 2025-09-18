import { useNewChatRoom, useSessionUser } from '@metafox/chatplus/hooks';
import { getFriends } from '@metafox/chatplus/selectors';
import { RoomType, SuggestionItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import {
  Autocomplete,
  Box,
  Button,
  Chip,
  TextField,
  styled
} from '@mui/material';
import { debounce, differenceBy, isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import Avatar from '../Avatar';

const name = 'ChatRoomPanel';

const Root = styled('div')(({ theme }) => ({
  borderLeft: theme.mixins.border('divider'),
  backgroundColor: theme.palette.background.paper,
  display: 'flex',
  flexDirection: 'column',
  width: '100%',
  height: '100%'
}));

const MainContent = styled('div', { name, slot: 'mainContent' })(
  ({ theme }) => ({
    borderBottom: theme.mixins.border('divider'),
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center'
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
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: theme.typography.fontWeightMedium,
  padding: theme.spacing(3.5, 2),
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
    zIndex: theme.zIndex.speedDial,
    display: 'flex',
    alignItems: 'center',
    listStyle: 'none',
    '& fieldset': {
      border: 'none'
    }
  })
);

const WrapperSuggest = styled('div', {
  shouldForwardProp: props => props !== 'suggestList'
})<{ suggestList?: boolean }>(({ theme, suggestList }) => ({
  ...(suggestList === true && {
    position: 'absolute',
    border: theme.mixins.border('secondary'),
    backgroundColor: theme.palette.background.paper,
    borderRadius: theme.shape.borderRadius,
    marginTop: theme.spacing(0.5),
    width: '100%'
  })
}));
const ListItemChip = styled('li')(({ theme }) => ({
  margin: theme.spacing(0.5)
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

const ButtonCustom = styled(Button, { name, slot: 'ButtonSubmit' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(0.5)
  })
);
const InputStyled = styled(TextField, {
  name,
  slot: 'InputStyled',
  shouldForwardProp: props => props !== 'isFocus'
})<{ isFocus?: boolean }>(({ theme, isFocus }) => ({
  minHeight: theme.spacing(5),
  borderRadius: theme.spacing(2.5),
  paddingRight: theme.spacing(1),
  backgroundColor: theme.palette.action.hover,
  transition: 'all 300ms cubic-bezier(0.4, 0, 0.2, 1) 0ms',
  border: theme.mixins.border('secondary'),
  '& .MuiInputBase-root': {
    padding: theme.spacing(0, 1),
    '& input': {
      minHeight: theme.spacing(5),
      padding: '0 !important'
    }
  },
  '& .ico.ico-search-o': {
    opacity: 0.43
  },
  ...(isFocus && {
    borderRadius: theme.spacing(2.5),
    border: theme.mixins.border('primary'),
    borderColor: theme.palette.primary.main,
    backgroundColor: theme.palette.background.paper,
    '& .ico.ico-search-o': {
      opacity: 1,
      color: theme.palette.primary.main
    }
  })
}));

const IconSearchStyled = styled(LineIcon, { name, slot: 'icon-search' })(
  ({ theme }) => ({
    padding: theme.spacing(0, 1)
  })
);

function NoConversation() {
  const { i18n, dispatch } = useGlobal();

  const [users, setUsers] = React.useState<SuggestionItemShape[]>([]);

  const { results, searchText } = useNewChatRoom();
  const userSession = useSessionUser();
  const friends = useSelector(getFriends);
  const [valueSearch, setValueSearch] = React.useState<string>('');
  const [openSuggest, setOpenSuggest] = React.useState<boolean>(false);
  const [isFocus, setFocus] = React.useState(false);
  const [isSubmitting, setIsSubmitting] = React.useState(false);

  const onSubmit = React.useCallback(() => {
    const memberCount = users.length;

    if (memberCount) {
      setIsSubmitting(true);
      dispatch({
        type: 'chatplus/newChatRoom/submit',
        payload: { users, pageMessage: true },
        meta: {
          onSuccess: () => {
            setIsSubmitting(false);
          },
          onFailure: () => {
            setIsSubmitting(false);
          }
        }
      });
    }
  }, [dispatch, users]);

  const onSelectItem = (user?: SuggestionItemShape) => {
    setValueSearch(undefined);
    setUsers(prev => [...prev, user]);
    handleClickAway();
  };

  const onRemoveItem = (user: SuggestionItemShape) => {
    setUsers(prev => prev.filter(x => x.value !== user.value));
  };

  const handleChangeSearch = React.useCallback(
    (evt: any) => {
      const query = evt.currentTarget?.value;
      setOpenSuggest(true);
      setValueSearch(query);

      if (!query) {
        const listFriend = Object.values(friends)
          ? Object.values(friends)?.slice(0, 4)
          : [];

        dispatch({
          type: 'chatplus/newChatRoom/search/updateUsers',
          payload: listFriend
        });

        return;
      }

      debounceSearch(query);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [friends]
  );

  const fetchData = query => {
    dispatch({ type: 'chatplus/newChatRoom/search', payload: { query } });
  };

  const debounceSearch = debounce(fetchData, 300);

  const resultsUser = results.users
    .filter(user => user.username !== userSession.username)
    .map(x => ({
      value: x.username,
      label: x.name,
      _id: x._id,
      avatarETag: x?.avatarETag
    }));

  const suggest: any = differenceBy(resultsUser, users, 'value');

  const handleClickAway = () => {
    setOpenSuggest(false);
  };

  const handleFocus = () => {
    setFocus(true);
  };

  const handleBlur = () => {
    setFocus(false);
  };

  return (
    <Root>
      <MainContent>
        <Title>{i18n.formatMessage({ id: 'new_message' })}</Title>

        <ClickOutsideListener onClickAway={handleClickAway}>
          <SearchWrapper>
            <Box sx={{ position: 'relative', flex: 1 }}>
              <Autocomplete
                sx={{ flex: 1 }}
                multiple
                open={false}
                disableClearable
                clearText={i18n.formatMessage({ id: 'clear' })}
                options={suggest}
                value={users as any[]}
                freeSolo
                renderTags={(values: readonly any[], getTagProps) => {
                  if (isEmpty(values)) return null;

                  return users.map((user: any, index: number) => (
                    <ListItemChip key={index}>
                      <Chip
                        variant="outlined"
                        label={user.label}
                        onDelete={() => onRemoveItem(user)}
                        size="medium"
                      />
                    </ListItemChip>
                  ));
                }}
                inputValue={valueSearch || searchText}
                renderInput={params => (
                  <InputStyled
                    {...params}
                    isFocus={isFocus}
                    onFocus={handleFocus}
                    onBlur={handleBlur}
                    onChange={handleChangeSearch}
                    placeholder={i18n.formatMessage({
                      id: 'type_name_start_new_conversation'
                    })}
                    InputProps={{
                      ...params.InputProps,
                      startAdornment: (
                        <>
                          <IconSearchStyled icon="ico-search-o" />
                          {params.InputProps.startAdornment}
                        </>
                      )
                    }}
                  />
                )}
              />
              {openSuggest ? (
                <WrapperSuggest
                  suggestList={Boolean(suggest && suggest.length)}
                >
                  <div>
                    <ScrollContainer autoHide autoHeight>
                      {suggest.map((user, idx) => (
                        <React.Fragment key={idx}>
                          <ItemSuggest onClick={() => onSelectItem(user)}>
                            <Avatar
                              name={user.label}
                              username={user.value}
                              size={32}
                              roomType={RoomType.Direct}
                              avatarETag={user?.avatarETag}
                            />
                            <ItemSuggestName>{user.label}</ItemSuggestName>
                          </ItemSuggest>
                        </React.Fragment>
                      ))}
                    </ScrollContainer>
                  </div>
                </WrapperSuggest>
              ) : null}
            </Box>
            <ButtonCustom
              disabled={!users.length || isSubmitting}
              size="medium"
              color="primary"
              variant="text"
              onClick={onSubmit}
            >
              {i18n.formatMessage({ id: 'submit' })}
            </ButtonCustom>
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
