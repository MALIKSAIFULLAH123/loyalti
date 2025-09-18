import { MsgItemShape, ChatMsgPassProps } from '@metafox/chat/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import MsgAvatar from './MsgAvatar';
import MsgItemFilter from './MsgItemFilter';

const name = 'MessageFilter';

const UIChatMsgSet = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isAlert' && prop !== 'isGroup'
})<{
  isOwner?: boolean;
  isAlert?: boolean;
  isGroup?: boolean;
}>(({ theme, isOwner, isAlert }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(0.5, 2),
  ...(isAlert && {
    '&.uiChatMsgSetAvatar': {
      display: 'none'
    },
    '&.uiChatMsgItemCall': {
      border: 'none'
    },
    '&.uiChatMsgActions': {
      justifyContent: 'center',
      '&.my-1; .btn': {
        '&.mx-1': '!important'
      }
    }
  }),
  ...(isOwner && { flexDirection: 'row-reverse' })
}));

const UIChatMsgSetAvatar = styled('div', {
  name,
  slot: 'uiChatMsgSetAvatar'
})(({ theme }) => ({
  marginRight: theme.spacing(1)
}));
const NoItemFound = styled('div', {
  name,
  slot: 'noItemFound'
})(({ theme }) => ({
  ...theme.typography.body1,
  padding: theme.spacing(1, 2),
  color: theme.palette.grey['600']
}));
interface MessageFilterProps extends ChatMsgPassProps {
  items: Array<MsgItemShape>;
}

const MessageFilter: React.FC<MessageFilterProps> = ({
  disableReact,
  room,
  items,
  handleAction
}) => {
  const { i18n } = useGlobal();

  if (isEmpty(items))
    return (
      <NoItemFound>
        {i18n.formatMessage({ id: 'no_results_found' })}{' '}
      </NoItemFound>
    );

  return (
    <div>
      {items &&
        Object.values(items).map((item, index) => (
          <UIChatMsgSet key={item.id.toString()}>
            <UIChatMsgSetAvatar>
              <MsgAvatar user={item.user} size={32} showTooltip />
            </UIChatMsgSetAvatar>
            <MsgItemFilter
              msgId={item.id}
              disableReact={disableReact}
              room={room}
              handleAction={handleAction}
              firstIndex={index === 0}
            />
          </UIChatMsgSet>
        ))}
    </div>
  );
};

export default MessageFilter;
