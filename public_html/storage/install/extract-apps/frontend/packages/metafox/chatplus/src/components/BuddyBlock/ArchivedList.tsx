import { RoomItemShape } from '@metafox/chatplus';
import { getArchivedChatsSelector } from '@metafox/chatplus/selectors';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import BuddyItem from './BuddyItem';

const name = 'ArchivedList-ChatRoom';
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
    padding: theme.spacing(1.5, 2),
    cursor: 'pointer',
    '& .ico': {
      display: 'inline-block',
      width: theme.spacing(4),
      height: theme.spacing(4),
      lineHeight: '32px',
      backgroundColor:
        theme.palette.mode === 'dark'
          ? theme.palette.grey['400']
          : theme.palette.grey['300'],
      borderRadius: '50%',
      color: theme.palette.grey['600'],
      textAlign: 'center',
      marginRight: theme.spacing(1)
    },
    '& span': {
      ...theme.typography.h5
    }
  })
);

const ArchivedMessagesLists = ({ archivedMessages }: any) => {
  const { i18n } = useGlobal();
  const [limit, setLimit] = React.useState(20);

  const clickMore = React.useCallback(() => {
    setLimit(pre => pre + 10);
  }, []);

  return (
    <div>
      {archivedMessages?.slice(0, limit).map(item => (
        <BuddyItem item={item} key={item.id} />
      ))}
      {archivedMessages.length > 20 ? (
        <MoreConversations onClick={clickMore}>
          <LineIcon icon={'ico-angle-down'} />
          <span>{i18n.formatMessage({ id: 'more_conversations' })}</span>
        </MoreConversations>
      ) : null}
    </div>
  );
};

interface Props {
  searchValue?: string;
}

function ArchivedList({ searchValue }: Props) {
  const { i18n } = useGlobal();

  const archivedMessages = useSelector<GlobalState, RoomItemShape[]>(state =>
    getArchivedChatsSelector(state, searchValue)
  );

  if (!archivedMessages.length) {
    return <NoMessages>{i18n.formatMessage({ id: 'no_messages' })}</NoMessages>;
  }

  if (searchValue && !archivedMessages.length) {
    return (
      <NoResult>{i18n.formatMessage({ id: 'no_results_found' })}</NoResult>
    );
  }

  return <ArchivedMessagesLists archivedMessages={archivedMessages} />;
}

export default ArchivedList;
