/**
 * @type: dialog
 * name: announcement.dialog.listViewer
 */

import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import React from 'react';
import { ScrollContainer } from '@metafox/layout';

export type ListViewDialogProps = {
  apiUrl: string;
  apiParams: Record<string, any>;
  dialogTitle: string;
  pagingId: string;
};

export default function ListViewDialog({
  apiUrl,
  apiParams,
  pagingId,
  dialogTitle
}: ListViewDialogProps) {
  const { useDialog, ListView, i18n, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const dataSource = { apiUrl, apiParams };
  const { dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: dialogTitle })}</DialogTitle>
      <DialogContent
        variant="fitScroll"
        sx={{ maxHeight: isMobile ? 'unset' : '50vh' }}
      >
        <ScrollContainer>
          <ListView
            dataSource={dataSource}
            pagingId={pagingId}
            canLoadMore
            clearDataOnUnMount
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            itemView="announcement.itemView.userRead"
          />
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}
