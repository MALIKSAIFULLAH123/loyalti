/**
 * @type: dialog
 * name: captcha_image.dialog.Form
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { ConfirmParams, TModalDialogProps } from '@metafox/dialog';
import { Dialog } from '@mui/material';
import React from 'react';
import { RemoteFormBuilder } from '@metafox/form';
interface Props extends ConfirmParams, TModalDialogProps {
  config?: Record<string, any>;
}

export default function CaptchaImageDialog({ config }: Props) {
  const {
    action_name,
    onSubmit,
    formValues: formValuesProps = {},
    onCancel: onCancelProp
  } = config || {};
  const { useDialog, dispatch } = useGlobal();
  const {
    setDialogResolveValue,
    setDialogValue,
    dialogProps,
    forceClose,
    disableBackdropClick
  } = useDialog();
  const dataSource = useResourceAction('captcha', 'captcha', 'getVerifyForm');
  // cache formValue when dialog existed
  const formValues = React.useMemo(() => formValuesProps, []);

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onHandleSubmit = (values, form) => {
    setDialogResolveValue({ ...values, formValues });

    dispatch({
      type: '@waitingCaptchaImage/start',
      payload: { form, forceClose }
    });
  };

  const onCancel = () => {
    setDialogValue(null);
    onCancelProp && onCancelProp();
  };

  return (
    <Dialog
      {...dialogProps}
      fullScreen={false}
      data-testid="dialogCaptchaImage"
      maxWidth="xs"
      fullWidth
      aria-modal
      onCancel={onCancel}
    >
      <RemoteFormBuilder
        dataSource={dataSource}
        onSubmit={onSubmit || onHandleSubmit}
        onCancel={onCancel}
        pageParams={{
          action_name,
          auto_focus: true
        }}
        navigationConfirmWhenDirty={false}
        dialog
      />
    </Dialog>
  );
}
