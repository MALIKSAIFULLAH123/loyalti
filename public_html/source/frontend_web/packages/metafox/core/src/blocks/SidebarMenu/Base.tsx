import { useAppMenu, useGlobal, useLocation } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { SidebarMenuItem, UIBlockViewProps } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import clsx from 'clsx';
import React, { useCallback, useMemo } from 'react';
import useStyles from './styles';
import { isEmpty } from 'lodash';

export interface Props extends UIBlockViewProps {
  displayLimit: number;
}

export default function SidebarMenuBase({
  title,
  blockProps,
  displayLimit: limit = 8
}) {
  const { i18n, getAcl, useSession, getSetting, usePreference } = useGlobal();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const [open, setOpen] = React.useState<boolean>(false);
  const classes = useStyles();
  const primaryMenu = useAppMenu('core', 'primaryMenu');
  const { themeId } = usePreference();
  const location = useLocation();

  const toggleOpen = useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  const filteredItems = filterShowWhen(primaryMenu.items, {
    acl,
    setting,
    session,
    themeId,
    themeName: themeId.split(':')[0]
  });

  const items = useMemo(() => {
    if (isEmpty(filteredItems)) return [];

    if (filteredItems.length <= limit) {
      return filteredItems;
    } else if (open) {
      return filteredItems.filter(Boolean).concat({
        icon: 'ico-angle-up',
        testid: 'less',
        label: i18n.formatMessage({ id: 'less' }),
        onClick: toggleOpen
      });
    } else {
      return filteredItems.slice(0, limit).concat({
        icon: 'ico-angle-down',
        testid: 'more',
        label: i18n.formatMessage({ id: 'more' }),
        onClick: toggleOpen
      });
    }
  }, [filteredItems, limit, open, i18n, toggleOpen]);

  if (isEmpty(primaryMenu?.items) || isEmpty(items)) return null;

  const themeClass =
    themeId === 'transparency' ? classes.transparency : classes.paper;

  return (
    <Block testid="blockSidebarMenu">
      <BlockHeader title={title} />
      <BlockContent>
        <nav role="navigation">
          <div className={clsx(classes.menuList, themeClass)} role="menu">
            {items.map((item, index) => (
              <SidebarMenuItem
                {...item}
                variant="contained"
                key={index.toString()}
                iconVariant="iconVariantCircled"
                classes={classes}
                active={location.pathname === item.to}
              />
            ))}
          </div>
        </nav>
      </BlockContent>
    </Block>
  );
}
