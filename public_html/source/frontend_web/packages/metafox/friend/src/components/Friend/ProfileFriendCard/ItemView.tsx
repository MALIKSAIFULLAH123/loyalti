import { Link, useGlobal, useSession } from '@metafox/framework';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend/constant';
import { FriendRequestItemProps } from '@metafox/friend/types';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { Box, Button, styled } from '@mui/material';
import * as React from 'react';

const Root = styled(ItemView, { slot: 'root', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  })
);
const ItemContent = styled('div', { slot: 'ItemContent', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    maxWidth: '100%'
  })
);

const ItemMediaStyled = styled(ItemMedia, {
  slot: 'ItemMediaStyled',
  name: 'FriendItem'
})(({ theme }) => ({
  alignSelf: 'flex-start'
}));

const actionButtonList = [
  {
    id: FRIENDSHIP_CAN_ADD_FRIEND,
    text: 'add_friend',
    value: 'user/addFriend'
  },
  {
    id: FRIENDSHIP_REQUEST_SENT,
    text: 'cancel_request',
    value: 'user/cancelRequest'
  }
];

export default function FriendItem({
  identity,
  item,
  wrapProps,
  wrapAs,
  actions
}: FriendRequestItemProps) {
  const { user: authUser } = useSession();
  const { i18n, dispatch } = useGlobal();

  if (!item) return null;

  const { statistic, link: to, extra, friendship, profile_settings } = item;
  const isAuthUser = authUser?.id === item.id;
  const actionButton =
    friendship === FRIENDSHIP_CAN_ADD_FRIEND && !extra?.can_add_friend
      ? undefined
      : actionButtonList.find(button => button.id === item.friendship);

  const handleAction = value => {
    dispatch({
      type: value,
      payload: { identity }
    });
  };

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMediaStyled>
          <UserAvatar user={item} size={48} />
        </ItemMediaStyled>
        <ItemText>
          <ItemTitle lines={1}>
            <Link to={to} color={'inherit'} hoverCard={`/user/${item?.id}`}>
              {item.full_name}
            </Link>
          </ItemTitle>
          {profile_settings?.profile_view_profile &&
          !isAuthUser &&
          statistic?.total_mutual > 0 ? (
            <ItemSummary role="button" onClick={actions.showMutualFriends}>
              <Statistic values={statistic} display="total_mutual" />
            </ItemSummary>
          ) : null}
          {actionButton && (
            <Box mt={0.5}>
              <Button
                variant="outlined"
                color="primary"
                size="small"
                onClick={() => handleAction(actionButton.value)}
              >
                {i18n.formatMessage({ id: actionButton.text })}
              </Button>
            </Box>
          )}
        </ItemText>
      </ItemContent>
    </Root>
  );
}

FriendItem.displayName = 'FriendItemSmallCard';
