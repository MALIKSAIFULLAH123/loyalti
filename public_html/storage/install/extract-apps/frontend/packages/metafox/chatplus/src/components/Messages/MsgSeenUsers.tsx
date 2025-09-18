import { useChatUserItem } from '@metafox/chatplus/hooks';
import {
  MsgItemShape,
  SeenUserShape,
  UserShape
} from '@metafox/chatplus/types';
import { convertDateTime } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';
import MsgAvatar from './MsgAvatar';

const name = 'MsgSeenUser';

const UIChatMsgSeenUsers = styled('div', {
  name,
  slot: 'UIChatMsgSeenUsers',
  shouldForwardProp: props => props !== 'isOwner' && props !== 'indexLast'
})<{ isOwner?: boolean; indexLast?: boolean }>(
  ({ theme, isOwner, indexLast }) => ({
    display: 'flex',
    width: '100%',
    justifyContent: 'flex-end',
    alignItems: 'center',
    flexFlow: 'wrap',
    transform: 'translateX(12px)',
    marginBottom: theme.spacing(-1.5),
    marginTop: theme.spacing(0.5),
    ...(!isOwner
      ? {
          marginTop: theme.spacing(0),
          ...(indexLast && { transform: 'translateX(12px) translateY(-4px)' })
        }
      : {
          marginBottom: 0
        })
  })
);

const UIChatMsgListUsersMore = styled('span', {
  name,
  slot: 'UIChatMsgListUsersMore'
})(({ theme }) => ({
  background: theme.palette.grey['500'],
  color: theme.palette.grey['50'],
  borderRadius: 100,
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  height: theme.spacing(1.75),
  padding: theme.spacing(0, 0.5),
  fontSize: theme.spacing(1.5),
  lineHeight: theme.spacing(1.75)
}));

const AvatarWrapper = styled('div', {
  name,
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  marginRight: 0,
  marginLeft: theme.spacing(0.25),
  '& .MuiAvatar-root': {
    fontSize: `${theme.mixins.pxToRem(7)} !important`
  }
}));

export interface MsgSeenUsersProps {
  disabled: boolean;
  message: MsgItemShape;
  user: UserShape;
  isOwner: boolean;
  indexLast?: any;
}

function truncateString(str, num) {
  if (str.length > num) {
    return `${str.slice(0, num)}...`;
  } else {
    return str;
  }
}

const ItemSeenUser = ({ item, isOwner }: any) => {
  const { i18n } = useGlobal();

  const userInfo = useChatUserItem(item?._id);

  return (
    <Tooltip
      className={'uiChatTooltipSeenUser'}
      title={i18n.formatMessage(
        { id: 'seen_by_user_at_time' },
        {
          user: truncateString(item?.name || item?.username, 20),
          time: convertDateTime(item?.seenAt?.$date)
        }
      )}
      placement={'top'}
    >
      <AvatarWrapper isOwner={isOwner}>
        <MsgAvatar
          allowLink
          size={14}
          username={userInfo?.username || item?.username}
          name={userInfo?.name || item?.name}
          avatarETag={item?.avatarETag || userInfo?.avatarETag}
        />
      </AvatarWrapper>
    </Tooltip>
  );
};

export default function MsgSeenUsers({
  disabled,
  user,
  message: { u, seenUser },
  isOwner,
  indexLast
}: MsgSeenUsersProps) {
  const { dispatch } = useGlobal();

  if (disabled) return null;

  if (!seenUser?.length) return null;

  // filter not current session user
  const listSeenUsers = seenUser.filter(item => item._id !== user._id);

  if (!listSeenUsers.length) return null;

  const countSeenUserShow = 4;
  const moreSeenUser = listSeenUsers.length - countSeenUserShow;
  const moreUserList = listSeenUsers.slice(countSeenUserShow);

  const moreUserElement = moreSeenUser ? (
    <>
      {moreUserList.slice(0, 20).map((item, index) => (
        <div key={index}>{item?.name || item.username}</div>
      ))}
      {moreUserList.length > 20 ? <div>...</div> : null}
    </>
  ) : null;

  const showSeenUsersList = (users: SeenUserShape[]) => {
    dispatch({ type: 'chatplus/room/presentSeenUsersList', payload: users });
  };

  return (
    <UIChatMsgSeenUsers isOwner={isOwner} indexLast={indexLast}>
      {moreSeenUser > 0 ? (
        <Tooltip title={moreUserElement} placement={'top'}>
          <UIChatMsgListUsersMore
            onClick={() => showSeenUsersList(listSeenUsers)}
          >{`+${moreSeenUser}`}</UIChatMsgListUsersMore>
        </Tooltip>
      ) : null}
      {listSeenUsers.slice(0, countSeenUserShow).map((item, index) => (
        <Box
          key={index.toString()}
          onClick={() => showSeenUsersList(listSeenUsers)}
        >
          <ItemSeenUser item={item} isOwner={isOwner} />
        </Box>
      ))}
    </UIChatMsgSeenUsers>
  );
}
