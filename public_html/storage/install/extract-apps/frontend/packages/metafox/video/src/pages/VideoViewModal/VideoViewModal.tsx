/**
 * @type: dialog
 * name: video.dialog.videoView
 */

import { Dialog, DialogTitle, DialogContent } from '@metafox/dialog';
import { connectItem, useGlobal } from '@metafox/framework';
import * as React from 'react';
import { VideoItemShapeDialogProps } from '../../types';

function VideoViewDialog({
  item,
  identity,
  error,
  searchParams
}: VideoItemShapeDialogProps) {
  const { useDialog, useIsMobile, i18n, jsxBackend } = useGlobal();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile(true);

  if (!item) return null;

  return (
    <Dialog
      scroll={'body'}
      {...dialogProps}
      fullScreen={!error}
      data-testid="popupDetailVideo"
      onBackdropClick={undefined}
    >
      {isMobile || error ? (
        <DialogTitle
          backIcon="ico-close"
          enableBack={!error}
          disableClose={isMobile}
        >
          {i18n.formatMessage({ id: 'video' })}
        </DialogTitle>
      ) : null}
      <DialogContent
        sx={{
          padding: theme => (isMobile ? theme.spacing(2) : '0 !important'),
          height: isMobile ? 'auto' : '100% !important'
        }}
      >
        {jsxBackend.render({
          component: isMobile ? 'video.ui.detailMobile' : 'video.ui.detail',
          props: {
            identity,
            error,
            searchParams
          }
        })}
      </DialogContent>
    </Dialog>
  );
}

export default connectItem(VideoViewDialog);
