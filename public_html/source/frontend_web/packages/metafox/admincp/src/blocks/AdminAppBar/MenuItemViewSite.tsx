/**
 * @type: ui
 * name: appbar.item.viewSite
 * bundle: admincp
 */
import { LineIcon, MenuItemViewProps as Props } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import React from 'react';

const name = 'AdminCpItemViewSite';

const MenuItemViewSite = styled('a', { name, slot: 'UserAvatarButton' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: theme.palette.primary.main,
    border: 'none',
    position: 'relative',
    width: 'auto',
    padding: '15px 8px',
    fontSize: theme.mixins.pxToRem(15),
    '& i': {
      fontSize: theme.mixins.pxToRem(22),
      paddingLeft: theme.spacing(1.5)
    }
  })
);

const SmallMenuButton = styled('span', { name, slot: 'SmallMenuButton' })(
  ({ theme }) => ({
    cursor: 'pointer',
    margin: 0,
    padding: 0,
    color: theme.palette.text.secondary,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    height: '32px',
    width: '32px',
    fontSize: theme.mixins.pxToRem(22),
    '& .MuiBadge-badge': {
      marginTop: 0
    }
  })
);

export default function AsDivider({ item }: Props) {
  let url = process.env.MFOX_SITE_URL;
  const { i18n } = useGlobal();

  if (!url) {
    url = '/';
  }

  return (
    <MenuItemViewSite
      href={url}
      target="_blank"
      data-testid={item.testid || item.name}
      rel="noreferrer"
    >
      <SmallMenuButton>
        <Tooltip title={i18n.formatMessage({ id: item?.label }) || ''}>
          <LineIcon icon={item?.icon} component="i" />
        </Tooltip>
      </SmallMenuButton>
    </MenuItemViewSite>
  );
}
