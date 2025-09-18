/**
 * @type: dialog
 * name: saved.dialog.viewCollectionItemSaved
 */

import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import React from 'react';
import { Box } from '@mui/material';

export type Props = {
  dataSource: any;
  dialogTitle: string;
};

export default function ViewCollectionItemSaved({
  dataSource,
  dialogTitle
}: Props) {
  const { useDialog, ListView, i18n } = useGlobal();
  const { dialogProps } = useDialog();

  return (
    <Dialog
      data-testid="saved.dialog.viewCollectionItemSaved"
      {...dialogProps}
      maxWidth="xs"
      fullWidth
    >
      <DialogTitle>{i18n.formatMessage({ id: dialogTitle })}</DialogTitle>
      <DialogContent variant="fitScroll" sx={{ height: '45vh' }}>
        <ScrollContainer style={{ height: 'auto' }}>
          <Box p={1}>
            <ListView
              dataSource={dataSource}
              canLoadMore
              canLoadSmooth
              clearDataOnUnMount
              gridLayout="Friend List - Small List"
              itemLayout="Saved Collection List View"
              itemView="saved_collection_list.itemView.mainCard"
              emptyPage="hide"
            />
          </Box>
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}
