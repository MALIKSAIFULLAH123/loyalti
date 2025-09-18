import { useSessionUser } from '@metafox/chatplus/hooks';
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { ItemActionMenu, LineIcon, SearchBox } from '@metafox/ui';
import {
  Box,
  Button,
  IconButton,
  styled,
  Tooltip,
  Typography
} from '@mui/material';
import React from 'react';
import ArchivedList from './ArchivedList';
import BuddyList from './BuddyList';
import { actionsOnlineFriend } from './actions';
import useSpotlight from '@metafox/chatplus/hooks/useSpotlight';
import { camelCase } from 'lodash';

export interface Props extends BlockViewProps {}

const name = 'BuddyBlock';
const Root = styled(Box, {
  name,
  slot: 'root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  width: '100%',
  height: '100%',
  display: 'flex',
  flexDirection: 'column'
}));

const WrapperHeader = styled('div', {
  name,
  slot: 'WrapperHeader',
  overridesResolver: (props, styles) => [styles.wrapperHeader]
})(({ theme }) => ({}));
const LinkBack = styled('div', { name, slot: 'BackAllMessages' })(
  ({ theme }) => ({
    cursor: 'pointer',
    color: theme.palette.primary.main
  })
);

const Header = styled('div', {
  name,
  slot: 'Header',
  overridesResolver: (props, styles) => [styles.header]
})(({ theme }) => ({
  alignItems: 'center',
  boxSizing: 'border-box',
  display: 'flex',
  padding: theme.spacing(3, 2, 1),
  justifyContent: 'space-between'
}));

const HeaderArchived = styled('div', {
  name,
  slot: 'HeaderArchived',
  overridesResolver: (props, styles) => [styles.headerArchived]
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'flex-start',
  justifyContent: 'center',
  flexDirection: 'column',
  boxSizing: 'border-box',
  height: theme.spacing(9),
  padding: theme.spacing(1, 1, 1, 2)
}));
const HeaderActionStyled = styled('div')(({ theme }) => ({}));
const WrapperSearch = styled('div')(({ theme }) => ({
  padding: theme.spacing(2, 2, 1, 2)
}));
const WrapperButtonIcon = styled(Button)(({ theme }) => ({
  padding: theme.spacing(0, 1),
  height: 'auto',
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.primary,
  fontSize: theme.spacing(2.75),
  minWidth: 'auto',
  '& .ico-circle ': {
    fontSize: theme.spacing(1.75)
  },
  '& .ico-gear-o': {
    fontSize: theme.spacing(1.75)
  },
  '& .ico-inbox-o': {
    fontSize: theme.spacing(1.75)
  }
}));
const WrapperButtonIconMore = styled('span')(({ theme }) => ({
  display: 'inline-flex',
  textAlign: 'center',
  alignItems: 'center',
  justifyContent: 'center'
}));

const Content = styled('div', {
  name,
  slot: 'Content',
  overridesResolver: (props, styles) => [styles.content]
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(1, 0),
  flex: 1,
  minHeight: 0
}));

const SearchWrapper = styled(SearchBox, {
  name: 'SearchWrapper'
})(({ theme }) => ({
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

export default function Base(props: Props) {
  const { i18n, dispatch, useActionControl, usePageParams } = useGlobal();

  const scrollRef = React.useRef();
  const { searchText } = useSpotlight();
  const [searchValue, setSearchValue] = React.useState<string>(
    searchText || ''
  );

  const pageParams = usePageParams();

  const { rid } = pageParams;

  const [archivedMode, setArchivedMode] = React.useState(false);

  React.useEffect(() => {
    return () => {
      dispatch({
        type: 'chatplus/spotlight/clearSearching'
      });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    setSearchValue('');
  }, [archivedMode]);

  const [handleAction] = useActionControl<unknown, unknown>(null, {});

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleAction(type, payload, meta);

    if (type.includes('archivedChatMode')) {
      setArchivedMode(true);
    }
  };

  const userSession = useSessionUser();
  const dataActions = actionsOnlineFriend();

  const itemsActionStatus = React.useMemo(() => {
    return dataActions.map(item => {
      if (userSession?.status === item.item_name) {
        return { ...item, active: true };
      }

      return item;
    });
  }, [dataActions, userSession]);

  const handleClickNewGroup = () => {
    dispatch({
      type: 'chatplus/room/newGroup',
      payload: {}
    });
  };

  const handleClickNewConversation = () => {
    dispatch({
      type: 'chatplus/room/newConversationPage',
      payload: {}
    });
  };

  const onSearchInputChanged = evt => {
    if (evt && evt.target) {
      setSearchValue(evt.target.value);
    }
  };

  const handleResetSearch = () => {
    setSearchValue('');

    dispatch({
      type: 'chatplus/spotlight/clearSearching'
    });
  };

  if (archivedMode) {
    return (
      <Root>
        <WrapperHeader>
          <HeaderArchived>
            <LinkBack onClick={() => setArchivedMode(false)}>
              <Typography variant="body2">
                {i18n.formatMessage({ id: 'all_messages' })}
              </Typography>
            </LinkBack>
            <Typography component="h1" variant="h3">
              {i18n.formatMessage({ id: 'archived_groups' })}
            </Typography>
          </HeaderArchived>
          <WrapperSearch>
            <SearchWrapper
              placeholder={i18n.formatMessage({ id: 'search_archived_group' })}
              value={searchValue}
              onChange={onSearchInputChanged}
              endAdornment={
                searchValue ? (
                  <IconButton onClick={() => setSearchValue('')}>
                    <LineIcon icon="ico-close" />
                  </IconButton>
                ) : null
              }
            />
          </WrapperSearch>
        </WrapperHeader>
        <Content>
          <ScrollContainer
            autoHide
            autoHeight
            autoHeightMax={'100%'}
            ref={scrollRef}
          >
            <ArchivedList searchValue={searchValue} />
          </ScrollContainer>
        </Content>
      </Root>
    );
  }

  return (
    <Root data-testid={camelCase('chat room buddy')}>
      <WrapperHeader data-testid={camelCase('block header container')}>
        <Header data-testid={camelCase('chat room header')}>
          <Typography
            component="h1"
            variant="h3"
            color="textPrimary"
            data-testid={camelCase('block header title')}
          >
            {i18n.formatMessage({ id: 'messages' })}
          </Typography>
          <HeaderActionStyled data-testid={camelCase('block header action')}>
            <Tooltip
              title={i18n.formatMessage({ id: 'create_new_group' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                onClick={handleClickNewGroup}
                data-testid={camelCase('block header action new group')}
              >
                <LineIcon icon="ico-user2-three-o" />
              </WrapperButtonIcon>
            </Tooltip>
            <Tooltip
              title={i18n.formatMessage({ id: 'new_conversation' }) ?? ''}
              placement="top"
            >
              <WrapperButtonIcon
                onClick={handleClickNewConversation}
                data-testid={camelCase('block header new conversation')}
              >
                <LineIcon icon="ico-compose" />
              </WrapperButtonIcon>
            </Tooltip>
            <WrapperButtonIcon
              data-testid={camelCase('block header more action')}
            >
              <ItemActionMenu
                items={itemsActionStatus}
                disablePortal
                handleAction={handleActionLocalFunc}
                control={
                  <Tooltip
                    title={i18n.formatMessage({ id: 'more' })}
                    placement="top"
                  >
                    <WrapperButtonIconMore>
                      <LineIcon icon={'ico-dottedmore-o'} />
                    </WrapperButtonIconMore>
                  </Tooltip>
                }
              />
            </WrapperButtonIcon>
          </HeaderActionStyled>
        </Header>
        <WrapperSearch data-testid={camelCase('chat room search header')}>
          <SearchBox
            placeholder={i18n.formatMessage({ id: 'search_people_group' })}
            value={searchValue}
            onChange={onSearchInputChanged}
            endAdornment={
              searchValue ? (
                <IconButton onClick={() => setSearchValue('')}>
                  <LineIcon icon="ico-close" />
                </IconButton>
              ) : null
            }
          />
        </WrapperSearch>
      </WrapperHeader>
      <Content data-testid={camelCase('block chat list buddy')}>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={'100%'}
          ref={scrollRef}
        >
          <BuddyList
            searchValue={searchValue}
            rid={rid}
            handleResetSearch={handleResetSearch}
          />
        </ScrollContainer>
      </Content>
    </Root>
  );
}
