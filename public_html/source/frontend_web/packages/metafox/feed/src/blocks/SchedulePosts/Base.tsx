import { APP_FEED, RESOURCE_SCHEDULE } from '@metafox/feed';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import * as React from 'react';
import { AppState } from '../../types';

export type Props = BlockViewProps & AppState['profilePrivacy'];

export default function ReviewPost({
  title,
  canLoadMore,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  dataSource,
  ...rest
}: Props) {
  const { i18n, ListView, jsxBackend } = useGlobal();
  dataSource = useResourceAction(APP_FEED, RESOURCE_SCHEDULE, 'viewAll');

  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return React.createElement(EmptyPage);

  return (
    <ListView
      title={i18n.formatMessage({ id: title })}
      clearDataOnUnMount
      canLoadMore
      dataSource={{
        apiUrl: dataSource.apiUrl,
        apiParams: dataSource.apiParams
      }}
      gridVariant={gridVariant}
      emptyPage="core.block.no_content_with_icon"
      gridLayout="User - PendingPost - Main Card"
      itemLayout="User - PendingPost - Main Card"
      itemView={itemView}
      emptyPageProps={{
        title: i18n.formatMessage({ id: 'no_scheduled_posts' }),
        image: 'ico-clock-o'
      }}
      {...rest}
    />
  );
}
