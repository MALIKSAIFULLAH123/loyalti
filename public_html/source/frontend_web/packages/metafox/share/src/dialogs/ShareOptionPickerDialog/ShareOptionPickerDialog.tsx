/**
 * @type: dialog
 * name: share.dialog.ShareOptionPickerDialog
 */
import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle, useDialog } from '@metafox/dialog';
import { MenuItemShape } from '@metafox/ui';
import React from 'react';
import MenuItem from './MenuItem';

type Props = {
  shareOptions: MenuItemShape[];
  identity: string;
};

export default function ShareOptionPickerDialog(props: Props) {
  const { i18n, dispatch } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const { shareOptions, identity } = props;

  const handleClick = React.useCallback(
    (type: string) => {
      dispatch({ type, payload: { identity } });
      closeDialog();
    },
    [closeDialog, dispatch, identity]
  );

  return (
    <Dialog {...dialogProps}>
      <DialogTitle>{i18n.formatMessage({ id: 'share' })}</DialogTitle>
      <DialogContent sx={{ minWidth: 360, px: 0, py: 1 }}>
        {shareOptions.map((item, index) => (
          <MenuItem
            item={item}
            onClick={() => handleClick(item.value)}
            key={index.toString()}
          />
        ))}
      </DialogContent>
    </Dialog>
  );
}
