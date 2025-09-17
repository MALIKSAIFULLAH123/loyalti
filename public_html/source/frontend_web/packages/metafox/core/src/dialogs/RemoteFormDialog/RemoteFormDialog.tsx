/**
 * @type: dialog
 * name: core.dialog.RemoteForm
 */
import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilderProps, SmartFormBuilder } from '@metafox/form';
import { DialogProps } from '@mui/material';
import React from 'react';

type Props = {
  title?: string;
  maxWidth?: DialogProps['maxWidth'];
  fullWidth?: DialogProps['fullWidth'];
  dialogProps?: Partial<DialogProps>;
} & RemoteFormBuilderProps;

export default function RemoteForm({
  title = 'Edit',
  maxWidth = 'sm',
  fullWidth = true,
  dialogProps: dialogPropsOverride = {},
  ...rest
}: Props) {
  const { useDialog } = useGlobal();
  const dialogItem = useDialog();
  const { dialogProps } = dialogItem;

  return (
    <Dialog
      {...dialogProps}
      maxWidth={maxWidth}
      fullWidth={fullWidth}
      data-testid="editPopup"
      {...dialogPropsOverride}
    >
      <SmartFormBuilder
        dialog
        dialogTitle={title}
        dialogItem={dialogItem}
        {...rest}
      />
    </Dialog>
  );
}
