import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { APP_REPORT } from '@metafox/report';
import React from 'react';

export type Props = BlockViewProps;

const GroupReportContent = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  ...rest
}: Props) => {
  const { ListView, jsxBackend, usePageParams, compactData } = useGlobal();
  const { id } = usePageParams();
  const dataSource = useResourceAction(
    APP_REPORT,
    'report_owner',
    'viewAllReport'
  );
  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return <EmptyPage />;

  const { apiUrl, apiParams } = dataSource;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          emptyPage="core.block.no_content_with_icon"
          emptyPageProps={{
            title: 'no_group_member_report_content',
            description: 'no_group_member_report_content_description',
            image: 'ico-warning-o'
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

export default GroupReportContent;
