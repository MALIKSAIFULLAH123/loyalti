/**
 * @type: service
 * name: UserAvatarsGroup
 */
import { connectItem } from '@metafox/framework';
import { getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar } from '@mui/material';
import React from 'react';
import { UserAvatarsGroupProps } from '../types';

type AvatarItemProps = {
  item?: any;
  user?: any;
  className?: string;
};

const AvatarItem = ({ item, user, className }: AvatarItemProps) => {
  if (!item) return null;

  const name = item.full_name ?? user?.full_name;
  const avatar = getImageSrc(item.avatar ?? user?.avatar, '240');

  return (
    <Avatar
      component={'div'}
      alt={name}
      children={shortenFullName(name)}
      src={avatar}
      className={className}
    />
  );
};

const ConnectedItem = connectItem(AvatarItem);

export default function UserAvatarsGroup({
  classes,
  data,
  limit = 3
}: UserAvatarsGroupProps) {
  if (!data || !Array.isArray(data) || !data.length) {
    return null;
  }

  return data
    .slice(0, limit)
    .map(identity => (
      <ConnectedItem
        identity={identity}
        className={classes.userAvatarFriend}
        key={identity}
      />
    ));
}
