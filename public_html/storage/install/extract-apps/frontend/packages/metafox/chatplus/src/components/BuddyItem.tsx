import { RoomItemShape } from '@metafox/chatplus/types';
import { GlobalState, useGlobal } from '@metafox/framework';
import { FromNow } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import { getBuddyItem, getSubscriptionItem } from '../selectors';
import Avatar from './Avatar';

const RootBuddyItem = styled('div', {
  shouldForwardProp: props => props !== 'unread'
})<{ unread: number }>(({ theme, unread }) => ({
  paddingLeft: theme.spacing(2),
  paddingRight: theme.spacing(2),
  cursor: 'pointer',
  transition: 'background-color 300ms ease',
  ...(unread && {
    background: theme.palette.grey['100'],
    borderRadius: theme.spacing(0.5)
  })
}));
const ItemWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1.5, 0),
  color: theme.palette.grey['700'],
  fontSize: theme.spacing(1.75),
  cursor: 'pointer'
}));
const ItemMedia = styled('div')(({ theme }) => ({
  marginRight: theme.spacing(1)
}));
const ItemInner = styled('div')(({ theme }) => ({
  flex: 1,
  minWidth: '0'
}));
const ItemRowTitleWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  marginBottom: theme.spacing(0.5)
}));
const ItemTitle = styled('div')(({ theme }) => ({
  fontWeight: 'bold',
  fontSize: theme.spacing(1.75),
  lineHeight: theme.spacing(2.25),
  color: theme.palette.text.secondary,
  flex: 1,
  minWidth: 0,
  maxWidth: '100%',
  whiteSpace: 'nowrap',
  textOverflow: 'ellipsis',
  overflow: 'hidden'
}));
const ItemSubtitle = styled('div')(({ theme }) => ({
  color: theme.palette.text.secondary,
  display: 'inline-flex',
  alignItems: 'center'
}));
const UnReadDot = styled('span', { slot: 'UnReadDot' })(({ theme }) => ({
  display: 'inline-block',
  width: 12,
  height: 12,
  backgroundColor: theme.palette.primary.main,
  borderRadius: 20,
  marginTop: theme.spacing(0.25)
}));
const ItemMsgText = styled('div')(({ theme }) => ({
  display: 'block',
  color: theme.palette.text.secondary,
  padding: 0,
  minWidth: 0,
  maxWidth: '100%',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  fontSize: theme.spacing(1.5),
  lineHeight: theme.spacing(2),
  whiteSpace: 'nowrap'
}));

interface Props {
  item: RoomItemShape;
  classes: any;
}

export default function BuddyItem({ item, classes }: Props) {
  const { dispatch } = useGlobal();
  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, item.id)
  );
  const subscription = useSelector((state: GlobalState) =>
    getSubscriptionItem(state, item.id)
  );

  const openChatRoom = React.useCallback(
    () =>
      dispatch({
        type: 'chatplus/room/openBuddy',
        payload: { rid: item.id, userId: item.id }
      }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  const { name, avatar, username } = buddy || {};

  return (
    <RootBuddyItem unread={subscription.unread} onClick={openChatRoom}>
      <ItemWrapper>
        <ItemMedia>
          <Avatar name={name} username={username} size={32} src={avatar} />
        </ItemMedia>
        <ItemInner>
          <ItemRowTitleWrapper>
            <ItemTitle>{buddy.name}</ItemTitle>
            <ItemSubtitle>
              <FromNow value={item?.lastMessage?._updatedAt?.$date} />
            </ItemSubtitle>
          </ItemRowTitleWrapper>
          <ItemRowTitleWrapper>
            <ItemMsgText>{item?.lastMessage?.msg}</ItemMsgText>

            {subscription?.unread ? <UnReadDot /> : null}
          </ItemRowTitleWrapper>
        </ItemInner>
      </ItemWrapper>
    </RootBuddyItem>
  );
}
