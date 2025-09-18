import useSpotlight from '@metafox/chatplus/hooks/useSpotlight';
import { getGroupChatsSelector } from '@metafox/chatplus/selectors';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, styled } from '@mui/material';
import { camelCase, isEqual } from 'lodash';
import React, { memo } from 'react';
import { useSelector } from 'react-redux';
import BuddyItem from './BuddyItem';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'BuddyList-ChatRoom';
const NoResult = styled('div', { name, slot: 'NoResult' })(({ theme }) => ({
  padding: theme.spacing(1.5, 0),
  fontSize: theme.spacing(1.875),
  color: theme.palette.grey['600'],
  textAlign: 'center'
}));
const NoMessages = styled('div', { name, slot: 'NoMessages' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  padding: theme.spacing(1.5, 0),
  fontSize: theme.spacing(1.875),
  color: theme.palette.grey['600'],
  textAlign: 'center'
}));
const MoreConversations = styled('div', { name, slot: 'MoreConversations' })(
  ({ theme }) => ({
    padding: theme.spacing(2),
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    '& span': {
      ...theme.typography.h5
    },
    '& .ico': {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      width: theme.spacing(4.5),
      height: theme.spacing(4.5),
      backgroundColor:
        theme.palette.mode === 'dark'
          ? theme.palette.grey['400']
          : theme.palette.grey['300'],
      borderRadius: '50%',
      color: theme.palette.grey['600'],
      textAlign: 'center',
      marginRight: theme.spacing(1),
      fontWeight: theme.typography.fontWeightMedium,
      fontSize: theme.mixins.pxToRem(18)
    }
  })
);

const BlockCollapseRoot = styled('div', { name, slot: 'BlockCollapse' })(
  ({ theme }) => ({
    borderTop: theme.mixins.border('secondary')
  })
);
const BlockCollapseHeader = styled('div', {
  name,
  slot: 'BlockCollapseHeader'
})(({ theme }) => ({
  padding: theme.spacing(3, 2),
  display: 'flex',
  flexDirection: 'row',
  justifyContent: 'space-between',
  alignItems: 'center',
  cursor: 'pointer'
}));
const BlockTitle = styled('div', {
  name,
  slot: 'BlockTitle'
})(({ theme }) => ({
  ...theme.typography.body1,
  color: theme.palette.text.primary,
  fontWeight: '600'
}));

const ChatBotStyled = styled(Box, {
  name,
  slot: 'ChatBotBlock'
})(({ theme }) => ({}));

const DividerStyled = styled('div', {
  name,
  slot: 'ChatBotBlock'
})(({ theme }) => ({
  borderTop: theme.mixins.border('secondary'),
  margin: theme.spacing(1, 0)
}));

const BuddyLists = ({
  data,
  loading,
  handleResetSearch,
  active,
  setActive
}: any) => {
  const { i18n } = useGlobal();
  const [limit, setLimit] = React.useState(5);

  const clickMore = React.useCallback(() => {
    setLimit(pre => pre + 5);
  }, []);

  const lists = data?.slice(0, limit);

  if (loading) {
    return <LoadingSkeleton />;
  }

  return (
    <div data-testid={camelCase('block buddy list')}>
      {lists.map(item => (
        <BuddyItem
          item={item}
          key={item.id}
          handleResetSearch={handleResetSearch}
          active={active}
          setActive={setActive}
        />
      ))}
      {data.length > 5 ? (
        lists.length === data.length ? null : (
          <MoreConversations
            data-testid={camelCase('more conversation')}
            onClick={clickMore}
          >
            <LineIcon icon={'ico-angle-down'} />
            <span>{i18n.formatMessage({ id: 'more_conversations' })}</span>
          </MoreConversations>
        )
      ) : null}
    </div>
  );
};

const BlockCollapse = ({
  title,
  data,
  searchValue,
  roomIdActive,
  loading,
  handleResetSearch,
  active,
  setActive,
  defaultOpen = false
}: any) => {
  const { i18n } = useGlobal();

  const [open, setOpen] = React.useState(defaultOpen);
  const toggleOpen = React.useCallback(() => setOpen(open => !open), []);

  React.useEffect(() => {
    const openCollapse = data.filter(item => item.id === roomIdActive);

    if (searchValue || openCollapse?.length) {
      setOpen(true);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchValue, data]);

  const [limit, setLimit] = React.useState(5);

  const clickMore = React.useCallback(() => {
    setLimit(pre => pre + 5);
  }, []);

  if (loading) {
    return <LoadingSkeleton title={title} />;
  }

  if (!data.length) return null;

  return (
    <BlockCollapseRoot data-testid={camelCase('Block Collapse')}>
      <BlockCollapseHeader
        data-testid={camelCase('Block Collapse header')}
        onClick={toggleOpen}
      >
        <BlockTitle>{title}</BlockTitle>
        <IconButton
          data-testid={camelCase('Block Collapse Toggle')}
          size="small"
          color="default"
        >
          <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
        </IconButton>
      </BlockCollapseHeader>
      {open ? (
        <div data-testid={camelCase('block buddy list collapse')}>
          {data?.slice(0, limit).map(item => (
            <BuddyItem
              item={item}
              key={item?.id}
              handleResetSearch={handleResetSearch}
              active={active}
              setActive={setActive}
            />
          ))}
          {data.length > 5 ? (
            data?.slice(0, limit).length === data.length ? null : (
              <MoreConversations
                data-testid={camelCase('more conversation')}
                onClick={clickMore}
              >
                <LineIcon icon={'ico-angle-down'} />
                <span>{i18n.formatMessage({ id: 'more_conversations' })}</span>
              </MoreConversations>
            )
          ) : null}
        </div>
      ) : null}
    </BlockCollapseRoot>
  );
};

interface Props {
  rid?: string;
  searchValue?: string;
  handleResetSearch?: any;
}

function BuddyList({ rid, handleResetSearch, searchValue }: Props) {
  const { i18n, dispatch } = useGlobal();
  const { loading: loadingSpotlight } = useSpotlight();
  const { directChats, publicGroups, isFetchDone, chatBot } = useSelector<
    GlobalState,
    any
  >(state => getGroupChatsSelector(state, null, true));

  const data = React.useRef({
    directChats,
    publicGroups,
    chatBot,
    isFetchDone
  });

  const firstUpdate = React.useRef(true);
  const [active, setActive] = React.useState(null);

  React.useEffect(() => {
    if (
      !searchValue &&
      !isEqual(data.current, {
        directChats,
        publicGroups,
        chatBot,
        isFetchDone
      })
    ) {
      data.current = { directChats, publicGroups, chatBot, isFetchDone };
    }
  }, [directChats, publicGroups, chatBot, isFetchDone, data, searchValue]);

  React.useEffect(() => {
    if (firstUpdate.current) {
      firstUpdate.current = false;

      return;
    }

    dispatch({
      type: 'chatplus/spotlight',
      payload: { query: searchValue, users: true, rooms: true },
      meta: {
        onSuccess: values => {
          data.current = values;
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchValue]);

  const isDivider = React.useMemo(() => {
    if (searchValue) return false;

    if (!chatBot || !data.current.directChats.length) return false;

    return true;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchValue, chatBot, data.current.directChats]);

  if (
    data.current.isFetchDone &&
    !data.current.directChats.length &&
    !data.current.publicGroups.length &&
    !data.current?.chatBot
  ) {
    return <NoMessages>{i18n.formatMessage({ id: 'no_messages' })}</NoMessages>;
  }

  if (
    searchValue &&
    !data.current.directChats.length &&
    !data.current.publicGroups.length &&
    !data.current?.chatBot
  ) {
    return (
      <NoResult>{i18n.formatMessage({ id: 'no_results_found' })}</NoResult>
    );
  }

  return (
    <div>
      {!loadingSpotlight && data.current.chatBot ? (
        <ChatBotStyled data-testid={camelCase('block buddy chatbot')}>
          <BuddyItem
            item={data.current.chatBot}
            handleResetSearch={handleResetSearch}
            active={active}
            setActive={setActive}
          />
        </ChatBotStyled>
      ) : null}
      <Box>
        {isDivider ? <DividerStyled /> : null}
        <BuddyLists
          data={data.current.directChats}
          searchValue={searchValue}
          handleResetSearch={handleResetSearch}
          loading={!data.current.isFetchDone || loadingSpotlight}
          setActive={setActive}
          active={active}
        />
      </Box>
      <BlockCollapse
        title={i18n.formatMessage({ id: 'public_group_chats' }).toUpperCase()}
        data={data.current.publicGroups}
        searchValue={searchValue}
        roomIdActive={rid}
        handleResetSearch={handleResetSearch}
        loading={!data.current.isFetchDone || loadingSpotlight}
        setActive={setActive}
        active={active}
        defaultOpen
      />
    </div>
  );
}

export default memo(BuddyList);
