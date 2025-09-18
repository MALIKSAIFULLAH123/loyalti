/**
 * @type: dialog
 * name: comment.dialog.historyList
 */

import { Dialog, DialogContent, DialogTitle, useDialog } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { styled, CircularProgress, Box } from '@mui/material';
import React from 'react';

const name = 'HistoryDialog';

const DialogContentStyled = styled(DialogContent, {
  name,
  slot: 'DialogContentStyled'
})(({ theme }) => ({
  borderRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(0, 2),
  paddingBottom: 0,
  maxHeight: 290,
  [theme.breakpoints.down('sm')]: {
    borderRadius: 0
  }
}));

export default function HistoryDialog({ dataSource, pageParams }: any) {
  const { dialogProps } = useDialog();
  const { i18n, useFetchItems, jsxBackend } = useGlobal();
  const CommentHistoryItem = jsxBackend.get('CommentHistoryItem');
  const [data, loading] = useFetchItems({
    dataSource,
    pageParams,
    data: []
  });

  return (
    <Dialog {...dialogProps} fullWidth scroll="body">
      <DialogTitle>{i18n.formatMessage({ id: 'edit_history' })}</DialogTitle>
      <DialogContentStyled>
        {loading ? (
          <Box
            p={2}
            sx={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            <CircularProgress size={16} />
          </Box>
        ) : null}
        {data && data.length ? (
          <Box mt={2}>
            {data.map(item => (
              <Box mb={2} key={`k${item.id}`}>
                <CommentHistoryItem item={item} />
              </Box>
            ))}
          </Box>
        ) : null}
      </DialogContentStyled>
    </Dialog>
  );
}
