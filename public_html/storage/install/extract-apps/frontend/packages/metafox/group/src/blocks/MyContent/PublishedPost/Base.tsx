import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import {
  APP_GROUP,
  MENU_MY_CONTENT_NAME,
  PUBLISHED,
  APP_FEED
} from '@metafox/group';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';

export type Props = BlockViewProps;

const PendingPost = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  ...rest
}: Props) => {
  const { ListView, jsxBackend, usePageParams, compactData, i18n, useAppMenu } =
    useGlobal();
  const { id } = usePageParams();
  const dataSource = useResourceAction(
    APP_FEED,
    APP_FEED,
    'viewCreatorPublishedPost'
  );
  const menus: MenuItemShape[] = useAppMenu(
    APP_GROUP,
    MENU_MY_CONTENT_NAME
  ).items;

  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return React.createElement(EmptyPage);

  const itemMenu: MenuItemShape =
    menus[menus.findIndex(item => item.name === PUBLISHED)];

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
            title: i18n.formatMessage({ id: 'no_published_post' }),
            image: itemMenu.icon
          }}
        />
      </BlockContent>
    </Block>
  );
};

export default PendingPost;
