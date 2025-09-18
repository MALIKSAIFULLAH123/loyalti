import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { APP_GROUP } from '@metafox/group';
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
  const { ListView, jsxBackend, usePageParams, compactData, i18n } =
    useGlobal();
  const { id } = usePageParams();
  const dataSource = useResourceAction(APP_GROUP, APP_GROUP, 'viewPendingPost');
  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return React.createElement(EmptyPage);

  const { apiUrl, apiParams } = dataSource;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          emptyPage="core.block.no_content_with_icon"
          emptyPageProps={{
            title: i18n.formatMessage({ id: 'no_pending_post' }),
            description: i18n.formatMessage({
              id: 'there_are_no_pending_post'
            }),
            image: 'ico-file-text-o'
          }}
          canLoadMore
          dataSource={{
            apiUrl,
            apiParams: compactData(apiParams, { id })
          }}
          gridVariant={gridVariant}
          gridLayout={gridLayout}
          itemLayout={itemLayout}
          itemView={itemView}
        />
      </BlockContent>
    </Block>
  );
};

export default PendingPost;
