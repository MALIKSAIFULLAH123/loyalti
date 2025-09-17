import { useGlobal, getItemSelector, GlobalState } from '@metafox/framework';
import { ItemShape } from '@metafox/ui';
import { isArray, isEmpty, isString } from 'lodash';
import { useSelector } from 'react-redux';
import * as React from 'react';
import ItemNotFound from './ItemNotFound';
import { RESOURCE_SCHEDULE_EMBED } from '@metafox/feed';

export type FeedEmbedObjectViewProps = {
  feed?: ItemShape;
  embed?: string;
  setVisible?: (value: boolean) => void;
};

export default function FeedEmbedObjectView({
  feed,
  embed,
  setVisible
}: FeedEmbedObjectViewProps) {
  const { jsxBackend } = useGlobal();

  const isMedia =
    isString(embed) && ['photo', 'video']?.includes(embed?.split('.')[0]);

  const embedItem = useSelector<GlobalState>(state =>
    getItemSelector(state, embed)
  ) as ItemShape & { error?: string; message?: string; title?: string };

  if (embedItem?.error)
    return (
      <ItemNotFound
        description={embedItem.message}
        title={embedItem.title}
        sx={isMedia && { mt: 1.5 }}
      />
    );

  // sync delete without reload
  if (embed && isEmpty(embedItem)) return <ItemNotFound />;

  if (isArray(embed) && isEmpty(embed)) return <ItemNotFound />;

  if (!feed || !isString(embed)) return null;

  const resource =
    embedItem?.resource_name === RESOURCE_SCHEDULE_EMBED
      ? embedItem?.type_id
      : embed.split('.')[2];

  const EmbedObjectView = jsxBackend.get(
    `${resource}.embedItem.insideFeedItem`
  );

  if (!EmbedObjectView) return null;

  return React.createElement(EmbedObjectView, {
    identity: embed,
    'data-testid': 'feedEmbedObjectView',
    feed,
    setVisible
  });
}
