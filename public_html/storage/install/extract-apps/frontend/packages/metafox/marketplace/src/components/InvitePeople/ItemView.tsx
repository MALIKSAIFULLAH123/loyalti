import { Link } from '@metafox/framework';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { UserItemProps } from '@metafox/user/types';

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

export default function PeopleCard({
  identity,
  wrapProps,
  wrapAs,
  item,
  actions
}: UserItemProps) {
  if (!item) return null;

  const { link: to } = item;

  return (
    <Root
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item} size={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link to={to} children={item.full_name} color={'inherit'} />
        </ItemTitle>
      </ItemText>
    </Root>
  );
}

PeopleCard.LoadingSkeleton = LoadingSkeleton;
