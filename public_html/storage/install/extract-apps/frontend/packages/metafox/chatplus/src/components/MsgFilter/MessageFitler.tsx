import { ChatMsgPassProps } from '@metafox/chatplus';
import { useChatUserItem } from '@metafox/chatplus/hooks';
import { MsgItemShape } from '@metafox/chatplus/types';
import { useGlobal, useScrollRef } from '@metafox/framework';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import MsgAvatar from '../Messages/MsgAvatar';
import MsgItemFilter from './MsgItemFilter';

const name = 'MessageFilter';

const UIChatMsgSet = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isAlert' && prop !== 'isGroup'
})<{ isOwner?: boolean; isAlert?: boolean; isGroup?: boolean }>(
  ({ theme, isOwner, isAlert, isGroup }) => ({
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
  })
);

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
  color: theme.palette.grey['600'],
  marginTop: theme.spacing(2)
}));
interface MessageFilterProps extends ChatMsgPassProps {
  items: Array<MsgItemShape>;
  phraseNoContent?: string;
  type: string;
}

const ItemMsgFilter = ({ item, isMobile, ...rest }: any) => {
  const userInfo = useChatUserItem(item?.u?._id);

  return (
    <UIChatMsgSet>
      <UIChatMsgSetAvatar>
        <MsgAvatar
          name={item?.u?.name}
          username={item?.u?.username}
          avatarETag={item?.u?.avatarETag || userInfo?.avatarETag}
          size={32}
          showTooltip
        />
      </UIChatMsgSetAvatar>
      <MsgItemFilter msgId={item._id} isMobile={isMobile} {...rest} />
    </UIChatMsgSet>
  );
};

const MessageFilter = (
  {
    items,
    phraseNoContent = 'no_results_found',
    isMobile = false,
    ...rest
  }: MessageFilterProps,
  ref
) => {
  const { i18n } = useGlobal();
  const scrollRef = useScrollRef();

  const scrollToBottom = () => {
    const yOffset = 10;
    const y = scrollRef.current.scrollHeight + yOffset;

    scrollRef.current.scrollTo({ top: y });
  };

  React.useImperativeHandle(ref, () => {
    return {
      scrollToBottom: () => {
        scrollToBottom();
      }
    };
  });

  if (isEmpty(items))
    return (
      <NoItemFound>{i18n.formatMessage({ id: phraseNoContent })} </NoItemFound>
    );

  return (
    <div>
      {items &&
        Object.values(items).map((item, index) => (
          <ItemMsgFilter
            key={item.id.toString()}
            item={item}
            firstIndex={index === 0}
            isMobile={isMobile}
            endIndex={Object.values(items).length === index + 1}
            {...rest}
          />
        ))}
    </div>
  );
};

export default React.forwardRef(MessageFilter);
