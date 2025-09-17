/**
 * @type: dialog
 * name: core.dialog.alertReminderList
 */
import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { styled, Box } from '@mui/material';
import React from 'react';
import Item from './AlertReminderListItem';
interface Props {
  title: string;
  data: Array<any>;
  presentAlert: (x: any) => void;
  disableCloseAll?: boolean;
  handleDismissAll: () => void;
}
const name = 'AlertReminderList';
const Content = styled(Box, { name, slot: 'Content' })(({ theme }) => ({
  display: 'block'
}));

export default function AlertReminderList({
  title,
  data,
  presentAlert,
  disableCloseAll,
  handleDismissAll
}: Props) {
  const { useDialog, i18n } = useGlobal();
  const { dialogProps, disableBackdropClick, closeDialog } = useDialog();

  const onCancel = () => {
    closeDialog();
    handleDismissAll();
  };

  React.useEffect(() => {
    disableBackdropClick(true);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!data?.length) return null;

  return (
    <Dialog
      {...dialogProps}
      maxWidth="sm"
      fullWidth
      fullScreen={false}
      data-testid="dialogAlertReminderList"
    >
      <DialogTitle disableClose={disableCloseAll} onCancel={onCancel}>
        {i18n.formatMessage({ id: title || 'notifications' })}
      </DialogTitle>
      <DialogContent sx={{ padding: 0 }}>
        <Content>
          {data.map(x => (
            <Item key={x?.id} presentAlert={presentAlert} item={x}></Item>
          ))}
        </Content>
      </DialogContent>
    </Dialog>
  );
}
