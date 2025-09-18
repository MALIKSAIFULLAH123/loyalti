/**
 * @type: dialog
 * name: story.dialog.dialogMutedListing
 */

import React from 'react';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { APP_STORY, RESOURCE_STORY_MUTE } from '@metafox/story/constants';
import { camelCase } from 'lodash';

export default function DialogMutedListing() {
  const { useDialog, ListView, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const dataSource = useResourceAction(
    APP_STORY,
    RESOURCE_STORY_MUTE,
    'viewAll'
  );

  if (!dataSource) return null;

  return (
    <Dialog
      {...dialogProps}
      maxWidth="sm"
      fullWidth
      data-testid={camelCase('dialog story_you_have_muted')}
    >
      <DialogTitle>
        {i18n.formatMessage({
          id: 'story_you_have_muted'
        })}
      </DialogTitle>
      <DialogContent variant="fix" sx={{ px: 1, py: 1 }}>
        <ListView
          dataSource={dataSource}
          itemView="story.itemView.mutedItem"
          canLoadMore
          canLoadSmooth
          clearDataOnUnMount
          gridLayout="Story - Muted Card"
          itemLayout="Story - Muted Card"
          emptyPage="store.block.no_muted_with_icon"
          emptyPageProps={{
            noBlock: true,
            icon: 'ico-eye-alt',
            description: 'you_have_not_muted_any_stories'
          }}
        />
      </DialogContent>
    </Dialog>
  );
}
