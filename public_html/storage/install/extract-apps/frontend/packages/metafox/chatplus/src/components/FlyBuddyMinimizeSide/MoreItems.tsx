import { getBuddyItem, getSubscriptionItem } from '@metafox/chatplus/selectors';
import { OpenRoomShape } from '@metafox/chatplus/types';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { Box, IconButton, styled, Tooltip } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import Avatar from '../Avatar';
import { useRoomItem } from '@metafox/chatplus/hooks';

const name = 'MoreItems';

const BuddyItemMore = styled('div', { name, slot: 'BuddyItemMore' })(
  ({ theme }) => ({
    position: 'relative',
    cursor: 'pointer',
    marginTop: theme.spacing(1),
    '&:before': {
      content: '""',
      display: 'block',
      backgroundColor: 'rgba(0,0,0,.5)',
      width: '100%',
      height: '100%',
      borderRadius: '50%',
      position: 'absolute',
      top: 0,
      left: 0,
      zIndex: 2
    }
  })
);
const MoreNumber = styled('div', { name, slot: 'MoreNumber' })(({ theme }) => ({
  width: '48px',
  height: '48px',
  color: 'white',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  fontSize: '20px',
  fontWeight: theme.typography.fontWeightMedium,
  position: 'absolute',
  top: 0,
  left: 0,
  zIndex: 2
}));

const StyledTooltip = styled(
  ({ className, ...props }: { className?: string }) => (
    <Tooltip {...props} classes={{ popper: className }} />
  )
)(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    ...theme.typography.h5,
    backgroundColor: 'white',
    color: 'black',
    border:
      theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
    boxShadow: theme.shadows[2],
    padding: theme.spacing(1.2)
  },
  '& .MuiTooltip-arrow': {
    '&::before': {
      backgroundColor: 'white',
      border: theme.mixins.border('secondary'),
      boxShadow: theme.shadows[2]
    }
  }
}));

const StyledListUser = styled('div')(({ theme }) => ({
  width: '180px'
}));
const RootItem = styled('div')(({ theme }) => ({
  padding: theme.spacing(0.625),
  paddingRight: theme.spacing(0.25),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  '&:hover': {
    borderRadius: theme.spacing(1),
    backgroundColor:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['200']
        : theme.palette.grey['300'],
    cursor: 'pointer'
  }
}));

const StyledName = styled(TruncateText)(({ theme }) => ({
  overflow: 'hidden'
}));

const UnReadNumberStyled = styled(Box)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightRegular
}));

const WrapperContent = styled(Box)(({ theme }) => ({
  display: 'flex',
  flex: 1,
  overflow: 'hidden'
}));

const StyledIconButton = styled(IconButton)(({ theme }) => ({
  margin: 0,
  padding: 0,
  '&:hover': {
    backgroundColor:
      theme.palette.mode === 'dark'
        ? theme.palette.grey['300']
        : theme.palette.action.selected,
    cursor: 'pointer'
  },

  '& span.ico': {
    fontSize: theme.spacing(1.75),
    color: theme.palette.grey['A700']
  }
}));

interface Props {
  buddyList: OpenRoomShape[];
  limitDisplay: number;
}

const ItemUser = ({ item }: any) => {
  const { dispatch } = useGlobal();
  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item.rid)
  );
  const subscription = useSelector((state: GlobalState) =>
    getSubscriptionItem(state, item.rid)
  );

  const openChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chatplus/room/toggle',
        payload: { identity: item?.rid, isMarkRead: true }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  const closeChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chatplus/closePanel',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  return (
    <RootItem onClick={openChatRoom}>
      <WrapperContent>
        <StyledName lines={1}>{buddy?.name}</StyledName>
        <UnReadNumberStyled>({subscription?.unread})</UnReadNumberStyled>
      </WrapperContent>
      <StyledIconButton
        onClick={closeChatRoom}
        aria-label="action"
        size="small"
        color="secondary"
      >
        <LineIcon icon={'ico-close'} />
      </StyledIconButton>
    </RootItem>
  );
};

const ListUser = ({ restData }: any) => {
  const { i18n } = useGlobal();

  if (!restData || !restData.length)
    return <>{i18n.formatMessage({ id: 'more_user' })}</>;

  return (
    <StyledListUser>
      {restData.map((item, index) => (
        <ItemUser key={index} item={item} />
      ))}
    </StyledListUser>
  );
};

function MoreItems({ buddyList, limitDisplay }: Props) {
  const restData = React.useMemo(
    () => buddyList.slice(limitDisplay, buddyList.length),
    [buddyList, limitDisplay]
  );
  const buddyFirst = useSelector((state: GlobalState) =>
    getBuddyItem(state, restData?.[0]?.rid)
  );
  const room = useRoomItem(restData?.[0]?.rid);

  if (!restData.length) return null;

  const { name, avatar, username, avatarETag } = buddyFirst || {};

  return (
    <StyledTooltip title={<ListUser restData={restData} />} placement="right">
      <BuddyItemMore>
        <Avatar
          name={name}
          username={username}
          size={48}
          src={avatar}
          room={room}
          avatarETag={avatarETag}
        />
        <MoreNumber>+{buddyList.length - limitDisplay}</MoreNumber>
      </BuddyItemMore>
    </StyledTooltip>
  );
}

export default MoreItems;
