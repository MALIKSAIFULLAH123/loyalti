import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { isEmpty } from 'lodash';
import * as React from 'react';

export interface Props extends BlockViewProps {}

export default function Subscription({
  title,
  emptyPage,
  itemView,
  itemProps,
  gridItemProps,
  moduleName,
  resourceName,
  actionName,
  displayLimit
}: Props) {
  const { ListView, useSession, usePageParams, useGetItem, useFetchDetail } = useGlobal();
  const { user: authUser } = useSession();
  const pageParams = usePageParams();

  const { identity } = pageParams;
  const profile = useGetItem(identity);

  const dataSource = useResourceAction(moduleName, resourceName, actionName);

  const [data] = useFetchDetail({
    dataSource
  });

  if (authUser?.id !== profile?.id || isEmpty(data)) return;

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          dataSource={dataSource}
          gridItemProps={gridItemProps}
          itemProps={itemProps}
          itemView={itemView}
          limitItemsLoadSmooth={1}
          displayLimit={displayLimit}
          emptyPage={emptyPage}
          canLoadMore={false}
        />
      </BlockContent>
    </Block>
  );
}