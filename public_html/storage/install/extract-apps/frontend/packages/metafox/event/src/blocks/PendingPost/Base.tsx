import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';
import { APP_EVENT } from '@metafox/event';

export type Props = BlockViewProps & { actionName: string };

const PendingPost = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  dataSource,
  actionName = 'viewPendingPost',
  ...rest
}: Props) => {
  const { ListView, usePageParams, compactData, i18n } = useGlobal();
  const { id } = usePageParams();
  dataSource = useResourceAction(APP_EVENT, APP_EVENT, actionName);

  const { apiUrl, apiParams } = dataSource;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          {...rest}
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
          clearDataOnUnMount
        />
      </BlockContent>
    </Block>
  );
};

export default PendingPost;
