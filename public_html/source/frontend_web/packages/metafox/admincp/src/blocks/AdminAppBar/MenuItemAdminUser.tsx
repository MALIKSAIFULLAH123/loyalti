/**
 * @type: ui
 * name: appbar.item.adminUser
 * bundle: admincp
 */
import { useSession } from '@metafox/framework';
import { MenuItemViewProps as Props, UserAvatar } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'AdminCpItemAdminUser';

const UserAvatarButton = styled('a', { name, slot: 'UserAvatarButton' })(
  ({ theme }) => ({
    display: 'flex',
    padding: theme.spacing(1.5, 1)
  })
);

const UserAvatarStyled = styled(UserAvatar, { name, slot: 'UserAvatarStyled' })(
  ({ theme }) => ({
    width: 32,
    height: 32
  })
);

const UserAvatarRole = styled(Box, { name, slot: 'UserAvatarRole' })(
  ({ theme }) => ({
    color: theme.palette.text.secondary,
    fontSize: theme.mixins.pxToRem(12)
  })
);

const UserAvatarName = styled(Box, { name, slot: 'UserAvatarName' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(12)
  })
);

const UserAvatarInfo = styled(Box, { name, slot: 'UserAvatarInfo' })(
  ({ theme }) => ({
    paddingLeft: theme.spacing(1.5),
    display: 'none',
    flexDirection: 'column',
    justifyContent: 'center'
  })
);

export default function AsUserItem({ item }: Props) {
  const { user } = useSession();

  let to = user.link;

  if (to.startsWith('/')) {
    to = `${process.env.MFOX_SITE_URL}${to}`;
  }

  return (
    <UserAvatarButton
      role="button"
      target="_blank"
      rel="noopener noreferrer"
      data-testid={item.testid || item.name}
      href={to}
    >
      <UserAvatarStyled
        alt="User"
        user={user}
        size={40}
        noLink
        showStatus={false}
      />
      <UserAvatarInfo>
        <UserAvatarName>{'Admin'}</UserAvatarName>
        <UserAvatarRole>{'Administrator'}</UserAvatarRole>
      </UserAvatarInfo>
    </UserAvatarButton>
  );
}
