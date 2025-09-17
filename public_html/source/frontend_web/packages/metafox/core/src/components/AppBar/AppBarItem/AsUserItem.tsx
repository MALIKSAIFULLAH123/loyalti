/**
 * @type: ui
 * name: appbar.item.user
 * chunkName: appbarAs
 */
import { RouteLink, useSession } from '@metafox/framework';
import { MenuItemViewProps as Props, UserAvatar } from '@metafox/ui';
import React from 'react';

export default function AsUserItem({ item, classes }: Props) {
  const { user } = useSession();

  return (
    <RouteLink
      role="button"
      data-testid={item.testid || item.name}
      to={`/${user?.user_name}`}
      className={classes.userAvatarButton}
    >
      <UserAvatar user={user} size={40} noLink noStory showStatus={false} />
    </RouteLink>
  );
}
