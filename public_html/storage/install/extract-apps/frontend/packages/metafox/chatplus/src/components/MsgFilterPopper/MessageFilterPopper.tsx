import { ChatMsgPassProps } from '@metafox/chatplus';
import { MsgItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import { TypeActionPinStar } from '../ChatRoomPanel/Header/Header';
import MsgItemFilterPopper from './MsgItemFilterPopper';

const name = 'MessageFilterPopper';

const Root = styled('div', { name, slot: 'root' })(({ theme }) => ({
  width: '100%',
  '& :last-child': {
    borderBottom: 'none'
  }
}));

const NoItemFound = styled('div', {
  name,
  slot: 'noItemFound'
})(({ theme }) => ({
  ...theme.typography.body1,
  padding: theme.spacing(1, 2),
  color: theme.palette.grey['600'],
  margin: theme.spacing(1, 0),
  textAlign: 'center'
}));

interface MessageFilterProps extends ChatMsgPassProps {
  items: Array<MsgItemShape>;
  phraseNoContent?: string;
  type: TypeActionPinStar;
  closePopover?: any;
}

const MessageFilterPopper: React.FC<MessageFilterProps> = ({
  archived,
  settings,
  user,
  disableReact,
  room,
  items,
  phraseNoContent = 'no_results_found',
  type,
  closePopover
}) => {
  const { i18n } = useGlobal();

  if (isEmpty(items))
    return (
      <NoItemFound>{i18n.formatMessage({ id: phraseNoContent })} </NoItemFound>
    );

  return (
    <Root>
      {items &&
        Object.values(items).map(item => (
          <MsgItemFilterPopper
            key={item._id}
            msgId={item._id}
            archived={archived}
            settings={settings}
            perms={{}}
            isMobile={false}
            user={user}
            disableReact={disableReact}
            room={room}
            type={type}
            closePopover={closePopover}
          />
        ))}
    </Root>
  );
};

export default MessageFilterPopper;
