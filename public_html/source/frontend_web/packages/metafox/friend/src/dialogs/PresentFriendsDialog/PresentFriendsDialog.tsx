import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import React from 'react';

export type UserFriendsDialogProps = {
  apiUrl: string;
  apiParams: Record<string, any>;
  dialogTitle: string;
  pagingId: string;
};

export default function FriendsDialog({
  apiUrl,
  apiParams,
  pagingId,
  dialogTitle
}: UserFriendsDialogProps) {
  const { useDialog, ListView } = useGlobal();
  const dataSource = { apiUrl, apiParams };
  const { dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{dialogTitle}</DialogTitle>
      <DialogContent variant="fitScroll" sx={{ height: '45vh' }}>
        <ScrollContainer autoHeightMax={'100%'} autoHide autoHeight>
          <ListView
            dataSource={dataSource}
            pagingId={pagingId}
            canLoadMore
            clearDataOnUnMount
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            itemView="friend.itemView.smallCard"
          />
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}
