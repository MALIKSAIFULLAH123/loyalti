/**
 * @type: block
 * name: core.globalSearchFilterBlock
 * title: Global Search Menu
 * keywords: search
 * description: Display global search.
 */

import {
  BlockViewProps,
  createBlock,
  MenuShape,
  useAppMenu,
  useGlobal
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import useStyles from './styles';
import { APP_SEARCH } from '@metafox/search';

function BaseBlock({ title }: BlockViewProps) {
  const { usePageParams, compactUrl, jsxBackend } = useGlobal();
  const { q, tab, pathname } = usePageParams();
  const classes = useStyles();
  const menu: MenuShape = useAppMenu(APP_SEARCH, 'webCategoryMenu');

  const items = menu.items.map(x => ({
    ...x,
    to: `${x.to}?q=${q}`,
    active: tab === x.name
  }));

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <div>
          {items.map((item, index) =>
            jsxBackend.render({
              component: `menuItem.as.${item.as || 'sidebarLink'}`,
              props: {
                key: index.toString(),
                variant: 'contained',
                item: {
                  ...item,
                  to: item.to ? compactUrl(item.to, { q }) : undefined
                },
                classes,
                active: tab === item.name || pathname === item.to
              }
            })
          )}
        </div>
      </BlockContent>
    </Block>
  );
}

export default createBlock<BlockViewProps>({
  name: 'GlobalSearchFilterBlock',
  extendBlock: BaseBlock,
  defaults: {
    title: 'Global Search Filter Block',
    blockLayout: 'sidebar app menu'
  }
});
