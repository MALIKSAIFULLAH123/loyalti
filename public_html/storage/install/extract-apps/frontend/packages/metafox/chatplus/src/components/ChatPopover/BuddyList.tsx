/**
 * @type: ui
 * name: chatplus.ui.buddyList
 */
import { DISPLAY_LIMIT_POPOVER } from '@metafox/chatplus/constants';
import useSpotlight from '@metafox/chatplus/hooks/useSpotlight';
import { getGroupChatsSelector } from '@metafox/chatplus/selectors';
import { GlobalState, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { isEmpty, isEqual } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import LoadingSkeleton from '../BuddyBlock/LoadingSkeleton';
import BuddyItem from './BuddyItem';

const name = 'BuddyList-ChatRoom';

const NoResult = styled('div', { name, slot: 'NoResult' })(({ theme }) => ({
  padding: theme.spacing(1.5, 0),
  fontSize: theme.spacing(1.75),
  color: theme.palette.text.primary,
  textAlign: 'center'
}));

const NoMessages = styled('div', { name, slot: 'NoMessages' })(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  padding: theme.spacing(2, 0),
  fontSize: theme.spacing(1.875),
  color: theme.palette.grey['600'],
  textAlign: 'center'
}));

interface Props {
  searchValue?: string;
  closePopover?: any;
  handleResetSearch?: any;
}

const checkSortFavorite = false;

function BuddyList({ searchValue, closePopover, handleResetSearch }: Props) {
  const { i18n, dispatch } = useGlobal();
  const [active, setActive] = React.useState(null);

  const { loading: loadingSpotlight } = useSpotlight();

  const { directChats, publicGroups, isFetchDone, chatBot } = useSelector<
    GlobalState,
    any
  >(state => getGroupChatsSelector(state, null, checkSortFavorite));

  const data = React.useRef({
    directChats,
    publicGroups,
    isFetchDone,
    chatBot
  });

  const firstUpdate = React.useRef(true);

  React.useEffect(() => {
    if (firstUpdate.current) {
      firstUpdate.current = false;

      return;
    }

    dispatch({
      type: 'chatplus/spotlight',
      payload: {
        query: searchValue,
        users: true,
        rooms: true,
        checkSortFavorite
      },
      meta: {
        onSuccess: values => {
          data.current = values;
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchValue]);

  React.useEffect(() => {
    if (
      !searchValue &&
      !isEqual(data.current, {
        directChats,
        publicGroups,
        isFetchDone,
        chatBot
      })
    ) {
      data.current = { directChats, publicGroups, isFetchDone, chatBot };
    }
  }, [directChats, publicGroups, isFetchDone, chatBot, data, searchValue]);

  const rooms = React.useMemo(() => {
    const dataBot =
      chatBot && data.current?.chatBot ? [data.current?.chatBot] : [];

    if (!isEmpty(searchValue)) {
      return [
        ...dataBot,
        ...data.current.directChats,
        ...data.current.publicGroups
      ];
    } else {
      const result = [
        ...data.current.directChats,
        ...data.current.publicGroups
      ].sort(
        (roomA, roomB) => roomB?._updatedAt?.$date - roomA?._updatedAt?.$date
      );

      return [...dataBot, ...result];
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    chatBot,
    data.current.directChats,
    data.current.publicGroups,
    data.current?.chatBot,
    searchValue
  ]);

  if (isFetchDone && !rooms.length) {
    return <NoMessages>{i18n.formatMessage({ id: 'no_messages' })}</NoMessages>;
  }

  if (searchValue && !rooms.length) {
    return (
      <NoResult>{i18n.formatMessage({ id: 'no_results_found' })}</NoResult>
    );
  }

  if (loadingSpotlight) {
    return (
      <Box sx={{ px: 1 }}>
        <LoadingSkeleton />
      </Box>
    );
  }

  return (
    <div>
      {rooms.slice(0, DISPLAY_LIMIT_POPOVER).map((item: any) => (
        <BuddyItem
          setActive={setActive}
          active={active}
          key={item?.id}
          item={item}
          closePopover={closePopover}
          handleResetSearch={handleResetSearch}
        />
      ))}
    </div>
  );
}

export default BuddyList;
