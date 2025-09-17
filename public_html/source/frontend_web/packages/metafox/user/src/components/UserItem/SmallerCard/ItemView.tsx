import { useSession } from '@metafox/framework';
import { FriendRequestItemProps } from '@metafox/friend/types';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  Statistic,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { styled } from '@mui/material';
import * as React from 'react';

const Root = styled(ItemView, { slot: 'root', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    '.MuiGrid-root.MuiGrid-item:last-child &': {
      borderBottom: 'none'
    }
  })
);
const ItemContent = styled('div', { slot: 'ItemContent', name: 'FriendItem' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center'
  })
);

export default function SmallerCard({
  identity,
  user,
  wrapProps,
  wrapAs,
  actions
}: FriendRequestItemProps) {
  const { user: authUser } = useSession();

  if (!user) return null;

  const { statistic, link: to, profile_settings } = user;
  const isAuthUser = authUser?.id === user.id;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${user.resource_name}`}
      data-eid={identity}
    >
      <ItemContent>
        <ItemMedia>
          <UserAvatar user={user} size={48} />
        </ItemMedia>
        <ItemText>
          <ItemTitle>
            <UserName to={to} user={user} color={'inherit'} hoverCard={false} />
          </ItemTitle>
          {profile_settings?.profile_view_profile && !isAuthUser ? (
            <ItemSummary role="button">
              <Statistic values={statistic} display="total_mutual" />
            </ItemSummary>
          ) : null}
        </ItemText>
      </ItemContent>
    </Root>
  );
}
