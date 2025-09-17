import {
  SideMenuBlockProps,
  useAppMenu,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { filterShowWhen } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';

export type Props = Partial<SideMenuBlockProps> & {
  menuName?: string;
  appName?: string;
  displayTitle?: boolean;
  item: Record<string, any>;
  menuStyleName?: string;
};

export default function SideAppMenuSettingBlock({
  displayTitle,
  title,
  blockProps,
  appName,
  menuName,
  item
}: Props) {
  const classes = useStyles();
  const {
    usePageParams,
    useSession,
    getAcl,
    jsxBackend,
    compactUrl,
    getSetting
  } = useGlobal();
  const acl = getAcl();
  const { tab, id } = usePageParams();
  const { pathname } = useLocation();
  const session = useSession();
  const setting = getSetting();

  const { appName: pageAppName, module_name } = usePageParams();
  const menu = useAppMenu(
    appName || pageAppName || module_name || 'core',
    menuName
  );

  if (!menu?.items || !menu.items.length) return null;

  // filter based on showWhen property
  const itemFilter = filterShowWhen(menu.items, {
    setting,
    session,
    acl,
    item
  });

  // has any items ?
  if (!itemFilter.length) {
    return null;
  }

  return (
    <Block testid="blockSidebarMenu">
      {displayTitle && <BlockHeader title={title} />}
      <BlockContent>
        <div role="menu">
          {itemFilter.map((item, index) =>
            jsxBackend.render({
              component: `menuItem.as.${item.as || 'sidebarLink'}`,
              props: {
                key: index.toString(),
                variant: 'middle',
                item: {
                  ...item,
                  to: item.to ? compactUrl(item.to, { id }) : undefined
                },
                classes,
                active: (tab && tab === item.tab) || pathname === item.to
              }
            })
          )}
        </div>
      </BlockContent>
    </Block>
  );
}
