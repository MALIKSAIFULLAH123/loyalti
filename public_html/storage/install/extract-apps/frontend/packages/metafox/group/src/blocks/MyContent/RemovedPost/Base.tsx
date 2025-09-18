import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import {
  APP_GROUP,
  REMOVED,
  MENU_MY_CONTENT_NAME,
  APP_FEED
} from '@metafox/group';
import React from 'react';

export type Props = BlockViewProps;

const RemovedPost = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemView,
  itemLayout,
  ...rest
}: Props) => {
  const { ListView, jsxBackend, usePageParams, compactData, i18n, useAppMenu } =
    useGlobal();
  const { id } = usePageParams();
  const dataSource = useResourceAction(
    APP_FEED,
    APP_FEED,
    'viewCreatorRemovedPost'
  );
  const EmptyPage = jsxBackend.get('core.block.error404');

  const menus: MenuItemShape[] = useAppMenu(
    APP_GROUP,
    MENU_MY_CONTENT_NAME
  ).items;

  if (!dataSource) return React.createElement(EmptyPage);

  const itemMenu: MenuItemShape =
    menus[menus.findIndex(item => item.name === REMOVED)];

  const { apiUrl, apiParams } = dataSource;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          canLoadMore
          dataSource={{
            apiUrl,
            apiParams: compactData(apiParams, { id })
          }}
          gridVariant={gridVariant}
          gridLayout={gridLayout}
          itemLayout={itemLayout}
          clearDataOnUnMount
          itemView={itemView}
          emptyPage="core.block.no_content_with_icon"
          emptyPageProps={{
            title: i18n.formatMessage({ id: 'no_removed_post' }),
            image: itemMenu.icon
          }}
        />
      </BlockContent>
    </Block>
  );
};

export default RemovedPost;
