/**
 * @type: block
 * name: core.block.appFooterMenu
 * title: App Footer Menu
 * keywords: general
 * description: Display footer apps menu
 * chunkName: block.home
 */

import { createBlock, Link, useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { filterShowWhen } from '@metafox/utils';

const name = 'FooterAppMenu';

const Menus = styled('ul', {
  name,
  slot: 'itemFooterAppMenu',
  overridesResolver(props, styles) {
    return [styles.itemFooterAppMenu];
  }
})(({ theme }) => ({
  listStyle: 'none',
  padding: 0,
  margin: 0,
  display: 'block',
  marginBottom: theme.spacing(1),
  paddingBottom: theme.spacing(1),
  borderBottom: theme.mixins.border('secondary'),
  color: theme.palette.default.contrastText,
  borderWidth: '0.5px',
  '& > li': {
    display: 'inline-block',
    paddingRight: theme.spacing(3)
  }
}));

function FooterAppMenu({ color = 'inherit' }) {
  const { useAppMenu, getSetting, getAcl, useSession, usePageParams } =
    useGlobal();
  const params = usePageParams();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();

  const appMenu = useAppMenu('core', 'appFooterMenu');

  const menus = filterShowWhen(appMenu?.items, {
    acl,
    setting,
    session,
    params
  });

  if (!menus?.length) return null;

  return (
    <Block>
      <BlockContent>
        <Menus>
          {menus
            ? menus.map((item, index) => (
                <li key={index.toString()}>
                  <Link color={color} to={item.to} key={item.to}>
                    {item.label}
                  </Link>
                </li>
              ))
            : null}
        </Menus>
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: FooterAppMenu,
  defaults: {
    title: 'Footer Menu'
  },
  overrides: {
    noHeader: true
  }
});
