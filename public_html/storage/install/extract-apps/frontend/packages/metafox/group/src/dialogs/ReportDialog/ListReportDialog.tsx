import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import React from 'react';

export type ListReportDialogProps = {
  apiUrl: string;
  apiParams?: Record<string, any>;
  dialogTitle: string;
  pagingId: string;
};

export default function ListReportDialog({
  apiUrl,
  apiParams,
  pagingId,
  dialogTitle
}: ListReportDialogProps) {
  const { useDialog, ListView } = useGlobal();
  const dataSource = { apiUrl, apiParams };
  const { dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{dialogTitle}</DialogTitle>
      <DialogContent variant="fitScroll" sx={{ height: '45vh' }}>
        <ScrollContainer style={{ height: 'auto' }}>
          <ListView
            dataSource={dataSource}
            canLoadMore
            clearDataOnUnMount
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            itemView="group.itemView.reportCard"
          />
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}
