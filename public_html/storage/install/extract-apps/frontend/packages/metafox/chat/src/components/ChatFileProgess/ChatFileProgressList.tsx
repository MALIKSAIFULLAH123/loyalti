import { styled } from '@mui/material';
import React from 'react';
import ChatFileProgressItem from './ChatFileProgressItem';

const ChatProgressListWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  marginBottom: theme.spacing(0.5),
  padding: theme.spacing(0.5, 2)
}));

const ChatProgressList = styled('div', {
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  width: '100%',
  ...(isAllPage && {
    maxWidth: 'calc(~"100% - 50px")'
  })
}));

interface Props {
  data: any;
  eventInit?: any;
  isAllPage?: boolean;
}

function ChatFileProgressList({ data, eventInit, isAllPage = false }: Props) {
  const objectKeys = Object.keys(data);
  const items = objectKeys.map(value => {
    return { id: value, ...data[value] };
  });

  React.useEffect(() => {
    if (eventInit) {
      eventInit();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [items]);

  if (!items.length) return null;

  return (
    <ChatProgressListWrapper>
      <ChatProgressList isAllPage={isAllPage}>
        {items.map(item => (
          <ChatFileProgressItem {...item} key={item.id} isAllPage={isAllPage} />
        ))}
      </ChatProgressList>
    </ChatProgressListWrapper>
  );
}

export default ChatFileProgressList;
