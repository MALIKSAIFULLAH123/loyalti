/**
 * @type: ui
 * name: core.ui.footerList
 * title: Footer Menu List inline
 * keywords: general
 * experiment: true
 * description: Display footer menu include privacy, terms of use, ....
 * chunkName: block.home
 */

import { Link, useGlobal } from '@metafox/framework';
import { styled, Typography } from '@mui/material';
import React from 'react';
import { filterShowWhen } from '@metafox/utils';

type FooterMenuProps = {
  color?:
    | 'inherit'
    | 'initial'
    | 'primary'
    | 'secondary'
    | 'textPrimary'
    | 'textSecondary'
    | 'error';
  [key: string]: any;
};

const name = 'Footer';

const Content = styled(Typography, {
  name,
  slot: 'content'
})(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap',
  alignItems: 'center',
  '& > span': {
    marginRight: theme.spacing(2)
  }
}));

function FooterMenu({
  color = 'inherit',
  sx = {},
  variant = 'body2'
}: FooterMenuProps) {
  const { useAppMenu, getSetting, getAcl, useSession, usePageParams } =
    useGlobal();
  const params = usePageParams();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();

  const primaryFooterMenu = useAppMenu('core', 'primaryFooterMenu');
  const secondaryFooterMenu = useAppMenu('core', 'secondaryFooterMenu');

  const menuItemsLeft = filterShowWhen(primaryFooterMenu?.items, {
    acl,
    setting,
    session,
    params
  });

  const menuItemsRight = filterShowWhen(secondaryFooterMenu?.items, {
    acl,
    setting,
    session,
    params
  });

  const copyright = setting?.core?.general?.site_copyright;

  if (!menuItemsLeft && !menuItemsRight) return null;

  return (
    <Content component="div" color={color} variant={variant} sx={sx}>
      {menuItemsLeft
        ? menuItemsLeft.map((item, index) => (
            <span key={index.toString()}>
              <Link color={color} to={item.to} key={item.to}>
                {item.label}
              </Link>
            </span>
          ))
        : null}
      {menuItemsRight
        ? menuItemsRight.map((item, index) => (
            <span key={`k${index}`}>
              {item.name === 'copyright' ? (
                `${copyright} ${new Date().getFullYear()}`
              ) : (
                <Link color={color} to={item.to} key={item.to}>
                  {item.label}
                </Link>
              )}
            </span>
          ))
        : null}
    </Content>
  );
}

export default FooterMenu;
