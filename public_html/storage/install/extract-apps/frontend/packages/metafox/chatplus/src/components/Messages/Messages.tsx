import { MessagesContext } from '@metafox/chatplus/context';
import {
  ChatMsgPassProps,
  ChatRoomShape,
  MsgGroupShape
} from '@metafox/chatplus/types';
import { convertDateTime } from '@metafox/chatplus/utils';
import { useScrollRef } from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import ChatFileProgress from '../ChatFileProgess';
import MsgGroup from './MsgGroup';
import MsgTyping from './MsgTyping';
import { THRESHOLD_SCROLL } from '@metafox/chatplus/constants';

const name = 'Messages';

const DateTimeGroup = styled(Box, { name, slot: 'DateTimeGroup' })(
  ({ theme }) => ({
    textAlign: 'center',
    color: theme.palette.grey['600']
  })
);
interface MessagesProps extends ChatMsgPassProps {
  rid: string;
  typing: any[];
  groups: Record<string, MsgGroupShape>;
  groupIds: string[];
  newest: number;
  containerRef?: React.RefObject<HTMLDivElement>;
  roomProgress?: any;
  isAllPage?: boolean;
  chatRoom?: ChatRoomShape;
  showToolbar?: boolean;
  isSearch?: boolean;
}

function Messages(
  {
    rid,
    typing,
    groups,
    newest,
    disableReact,
    isMobile,
    tooltipPosition,
    settings,
    room,
    archived,
    seenUserShow,
    user,
    groupIds,
    isReadonly,
    isRoomOwner,
    isRoomLimited,
    containerRef,
    handleAction,
    roomProgress,
    isAllPage,
    subscription,
    chatRoom,
    showToolbar = true
  }: MessagesProps,
  ref
) {
  const { msgSearch } = chatRoom || {};
  const scrollRef = useScrollRef();
  const [initMessage, setInitMessage] = React.useState(!!groupIds?.length);

  const initRef = React.useRef(false);

  const scrollToBottom = () => {
    const yOffset = 10;

    if ((msgSearch && msgSearch?.mQuoteId) || !msgSearch?.id) {
      const y = scrollRef.current?.scrollHeight + yOffset;
      scrollRef.current?.scrollTo({ top: y });
    }
  };

  React.useEffect(() => {
    if (!isEmpty(msgSearch)) return;

    if (initRef.current || !initMessage) return;

    initRef.current = true;

    // make sure scroll bottom when dom change
    setTimeout(() => {
      scrollToBottom();
    }, 100);

    return () => {
      initRef.current = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid, initMessage]);

  React.useEffect(() => {
    setInitMessage(!!groupIds?.length);
  }, [groupIds?.length]);

  const scrollToBottomNewest = eleHeight => {
    if (scrollRef?.current) {
      const ele: any = scrollRef.current || {};

      if (
        ele.scrollHeight - eleHeight - ele.scrollTop - THRESHOLD_SCROLL <=
        ele.clientHeight
      ) {
        scrollToBottom();
      }
    }
  };

  React.useEffect(() => {
    if (msgSearch?.mode === 'quote') return;

    if (scrollRef?.current && typing) {
      const ele: any = scrollRef.current || {};

      if (
        ele.scrollHeight - ele.scrollTop - THRESHOLD_SCROLL <=
        ele.clientHeight
      ) {
        scrollToBottom();
      }
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [typing, msgSearch?.mode]);

  React.useImperativeHandle(ref, () => {
    return {
      scrollToBottom: () => {
        scrollToBottom();
      }
    };
  });

  if (!groups) {
    return null;
  }

  return (
    <MessagesContext.Provider value={{ scrollToBottomNewest }}>
      {groupIds.map((groupId, i) => (
        <div key={`k.0${i}`}>
          {groups[groupId] && convertDateTime(groups[groupId]?.ts?.$date) ? (
            <DateTimeGroup py={1}>
              {convertDateTime(groups[groupId]?.ts?.$date)}
            </DateTimeGroup>
          ) : null}

          <MsgGroup
            user={user}
            msgGroup={groups[groupId]}
            archived={archived}
            settings={settings}
            tooltipPosition={tooltipPosition}
            isMobile={isMobile}
            disableReact={disableReact}
            seenUserShow={seenUserShow}
            room={room}
            subscription={subscription}
            isReadonly={isReadonly}
            isRoomLimited={isRoomLimited}
            isRoomOwner={isRoomOwner}
            handleAction={handleAction}
            showToolbar={showToolbar}
          />
        </div>
      ))}
      {!isEmpty(roomProgress) ? (
        <ChatFileProgress
          data={roomProgress}
          eventInit={scrollToBottom}
          isAllPage={isAllPage}
        />
      ) : null}
      <MsgTyping typings={typing} />
    </MessagesContext.Provider>
  );
}

export default React.forwardRef(Messages);
