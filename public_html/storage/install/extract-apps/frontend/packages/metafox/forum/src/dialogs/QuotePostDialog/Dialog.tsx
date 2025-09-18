/**
 * @type: dialog
 * name: forum.dialog.QuotePostDialog
 */

import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import React from 'react';

export default function AttachPollDialog({ dataSource, parentPost }) {
  const { useDialog, jsxBackend } = useGlobal();
  const dialogItem = useDialog();
  const { dialogProps } = dialogItem;
  const EmbedItem = jsxBackend.render({
    component: 'forum_post.ui.quoteItem',
    props: {
      item: parentPost,
      hideAttachment: true
    }
  });

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <RemoteFormBuilder
        noHeader
        dataSource={dataSource}
        dialog
        dialogEmbedItem={EmbedItem}
        dialogItem={dialogItem}
        keepPaginationData
      />
    </Dialog>
  );
}
