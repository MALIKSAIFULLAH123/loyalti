/**
 * @type: dialog
 * name: announcement.dialog.ViewAnnouncement
 * chunkName: pages.announcement
 */

import { connectItem, useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { AnnouncementItemProps as Props } from '@metafox/announcement/types';
import * as React from 'react';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

function ViewAnnouncementDialog({
  item,
  identity,
  error
}: Props & { error: Record<string, any> }) {
  const { useDialog, jsxBackend, useIsMobile, i18n } = useGlobal();
  const { dialogProps } = useDialog();
  const DetailView = jsxBackend.get('announcement.block.ViewAnnouncement');
  const isMobile = useIsMobile();

  if (!item) return null;

  return (
    <Dialog {...dialogProps} scroll="body" data-testid="popupViewAnnouncement">
      <ErrorBoundary error={error}>
        <DialogTitle enableBack={isMobile} disableClose={isMobile}>
          {i18n.formatMessage({ id: 'announcement' })}
        </DialogTitle>
        <DialogContent
          sx={{ p: 0, maxWidth: '100%' }}
        >
          <DetailView item={item} identity={identity} />
        </DialogContent>
      </ErrorBoundary>
    </Dialog>
  );
}

export default connectItem(ViewAnnouncementDialog);
