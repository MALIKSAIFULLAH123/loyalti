import { useRoomItem } from '@metafox/chat/hooks';
import { OpenRoomShape } from '@metafox/chat/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText } from '@metafox/ui';
import { IconButton, styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'MoreItems';

const BuddyItemMore = styled('div', { name, slot: 'BuddyItemMore' })(
  ({ theme }) => ({
    position: 'relative',
    cursor: 'pointer'
  })
);
const MoreNumber = styled('div', { name, slot: 'MoreNumber' })(({ theme }) => ({
  width: '48px',
  height: '48px',
  marginTop: theme.spacing(1),
  backgroundColor: theme.palette.grey['500'],
  color: 'white',
  borderRadius: '50%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  fontSize: '20px',
  fontWeight: theme.typography.fontWeightMedium
}));

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
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
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  width: '100%',
  overflow: 'hidden',
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
  width: 'calc(100% - 32px)'
}));

const StyledIconButton = styled(IconButton)(({ theme }) => ({
  width: '32px',
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
  const { dispatch, useGetItem } = useGlobal();

  const room = useRoomItem(item.rid);

  const userIdentity = room?.other_members[0];

  const user = useGetItem(userIdentity);

  const openChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chat/room/toggle',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );
  const closeChatRoom = React.useCallback(
    () => {
      dispatch({
        type: 'chat/closePanel',
        payload: { identity: item?.rid }
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  return (
    <RootItem onClick={openChatRoom}>
      <StyledName lines={1} variant="h5">
        {user?.full_name || user?.name}
      </StyledName>
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

  return (
    <StyledTooltip title={<ListUser restData={restData} />} placement="right">
      <BuddyItemMore>
        <MoreNumber>+{buddyList.length - limitDisplay}</MoreNumber>
      </BuddyItemMore>
    </StyledTooltip>
  );
}

export default MoreItems;
