/**
 * @type: ui
 * name: StatusComposerAttatchedShareItem
 */

import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import { get } from 'lodash';
import React from 'react';

export default function StatusComposerAttatchedShareItem({
  composerRef,
  ...props
}: StatusComposerControlProps) {
  const { jsxBackend, useGetItem } = useGlobal();
  const config = get(composerRef.current.state, 'attachments.shareItem.value');
  const { embedView, identity, feed } = config || {};

  const embedData = useGetItem(identity);

  if (!config) return null;

  const EmbedView = jsxBackend.get(embedView);
  const EmbedErrorView = jsxBackend.get(
    'itemNotFound.embedItem.insideFeedItem'
  );

  if (!EmbedView) return null;

  if (embedData?.error && EmbedErrorView) {
    return (
      <div style={{ padding: 16, position: 'relative' }}>
        <EmbedErrorView
          description={embedData.message}
          title={embedData.title}
          identity={identity}
          feed={feed}
          isShared
          {...props}
        />
      </div>
    );
  }

  return (
    <div style={{ padding: 16, position: 'relative' }}>
      <EmbedView identity={identity} feed={feed} isShared {...props} />
    </div>
  );
}
