/**
 * @type: dialog
 * name: gettingStarted.dialog.start
 */

import { DialogContent, DialogTitle, Dialog } from '@metafox/dialog';
import { useGetItems, useGlobal } from '@metafox/framework';
import { Box, Button, Grid } from '@mui/material';
import React from 'react';
import TodoList from './TodoList';
import { usePagingDataTodoList } from '@metafox/gettingstarted/hooks';

type Props = {
  title: string;
  disableClose: boolean;
};

export default function GettingStarted({ title, disableClose }: Props) {
  const { useDialog, i18n, dialogBackend, useSession } = useGlobal();
  const { user } = useSession();
  const { dialogProps, closeDialog } = useDialog();
  const dataTodoList = usePagingDataTodoList();
  const { loading, ids } = dataTodoList || {};
  const dataList = useGetItems(ids);
  const firstItemNotDone = React.useMemo(
    () => dataList.find(item => !item.is_done),
    [dataList]
  );

  const getStarted = () => {
    closeDialog();
    dialogBackend.present({
      component: 'gettingStarted.dialog.steps',
      props: {
        currentStep: firstItemNotDone ? firstItemNotDone?.ordering : 1
      }
    });
  };

  return (
    <Dialog {...dialogProps} maxWidth="md" fullWidth>
      <DialogTitle disableClose={disableClose}>
        {i18n.formatMessage({ id: title || 'getting_started' })}
      </DialogTitle>
      <DialogContent>
        <Grid container pb={3}>
          <Grid item xs={12} p={2} sm={6}>
            <Box whiteSpace="break-spaces">
              {i18n.formatMessage(
                { id: 'getting_started_welcome_message' },
                { name: user?.full_name }
              )}
            </Box>
          </Grid>
          <Grid item xs={12} sm={6}>
            <TodoList closeTodo={closeDialog} />
          </Grid>
        </Grid>
        <Box display="flex" justifyContent="center">
          <Button
            variant="text"
            color="inherit"
            onClick={getStarted}
            disabled={loading}
          >
            {i18n.formatMessage({ id: 'get_started' })}
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
}
