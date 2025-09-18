import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { APP_GROUP } from '@metafox/group';
import React from 'react';

export type Props = BlockViewProps;

const GroupRule = ({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  ...rest
}: Props) => {
  const { ListView, jsxBackend, i18n } = useGlobal();
  const dataSource = useResourceAction(APP_GROUP, 'group_rule', 'viewAll');
  const EmptyPage = jsxBackend.get('core.block.no_results');

  if (!dataSource) return React.createElement(EmptyPage);

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          emptyPage="core.block.no_content_with_icon"
          emptyPageProps={{
            title: i18n.formatMessage({ id: 'no_rules_set_up' }),
            description: i18n.formatMessage({
              id: 'no_rules_set_up_description'
            }),
            image: 'ico-note-paper-o'
          }}
          canLoadMore
          dataSource={dataSource}
          gridVariant={gridVariant}
          gridLayout={gridLayout}
          itemLayout={itemLayout}
          itemView={itemView}
        />
      </BlockContent>
    </Block>
  );
};

export default GroupRule;
