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
import { Button, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { APP_USER } from '@metafox/user/constant';

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
    maxWidth: '100%',
    flex: 1,
    overflow: 'hidden',
    marginRight: theme.spacing(1)
  })
);
const ButtonStyled = styled(Button, {
  slot: 'ButtonStyled',
  name: 'FriendItem'
})(({ theme }) => ({
  fontSize: theme.spacing(1.625)
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
  const { i18n, dispatch, dialogBackend } = useGlobal();

  const isDialogMutualFriends = dialogBackend.items.some(
    item =>
      item.props.content.component === 'friend.dialog.presentMutualFriends'
  );

  if (!item) return null;

  const { statistic, link: to, module_name, profile_settings } = item;
  const isAuthUser = authUser?.id === item.id;

  const actionButton = actionButtonList.find(
    button => button.id === item.friendship
  );
  const isTypeUser = module_name === APP_USER;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMedia>
          <UserAvatar user={item} size={48} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <Link to={to} children={item.full_name} color={'inherit'} />
          </ItemTitle>
          {profile_settings?.profile_view_profile &&
          !isAuthUser &&
          statistic?.total_mutual > 0 ? (
            <ItemSummary
              role="button"
              onClick={isDialogMutualFriends ? null : actions.showMutualFriends}
            >
              <Statistic values={statistic} display="total_mutual" />
            </ItemSummary>
          ) : null}
        </ItemText>
      </ItemContent>
      {actionButton && isTypeUser && (
        <ButtonStyled
          variant="contained"
          color="primary"
          onClick={() =>
            dispatch({
              type: actionButton.value,
              payload: { identity: `user.entities.user.${item.id}` }
            })
          }
        >
          {i18n.formatMessage({ id: actionButton.text })}
        </ButtonStyled>
      )}
    </Root>
  );
}

FriendItem.LoadingSkeleton = LoadingSkeleton;
FriendItem.displayName = 'FriendItemSmallCard';
