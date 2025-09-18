/**
 * @type: dialog
 * name: dialog.chatplus.changeNameGroupDialog
 * chunkName: dialog.Chatplus
 */
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LoadingButton } from '@mui/lab';
import { Button, DialogActions, TextField } from '@mui/material';
import React from 'react';
import { MAX_LENGTH_NAME_GROUP } from '../constants';
import { getBuddyItem } from '../selectors';
import { useSelector } from 'react-redux';

type IProps = { room: any };

function ChangeNameGroupDialog({ room }: IProps) {
  const { useDialog, i18n, dispatch } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();
  const [loading, setLoading] = React.useState(false);

  const buddy = useSelector((state: GlobalState) =>
    getBuddyItem(state, room?.id)
  );
  const [nameChange, setNameChange] = React.useState<string | null>(
    buddy?.name
  );

  React.useEffect(() => {
    setNameChange(buddy?.name);
  }, [buddy?.name]);

  if (!room) {
    closeDialog();

    return null;
  }

  const onCancel = () => {
    closeDialog();
  };

  const onChangedName = e => {
    setNameChange(e.target.value);
  };

  const handleSaveName = () => {
    if (!nameChange) return;

    setLoading(true);
    dispatch({
      type: 'chatplus/room/editSettings',
      payload: { identity: room?.id, value: { roomName: nameChange } },
      meta: {
        onSuccess: handleSuccessSaveName,
        onFailure: handleFailureSaveName
      }
    });
  };

  const handleSuccessSaveName = () => {
    setNameChange(null);
    setLoading(false);
    closeDialog();
  };

  const handleFailureSaveName = () => {
    setNameChange(null);
    setLoading(false);
  };

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {i18n.formatMessage({ id: 'change_group_name' })}
      </DialogTitle>
      <DialogContent sx={{ maxHeight: '100%', py: 0.5 }}>
        <TextField
          onChange={onChangedName}
          autoFocus
          fullWidth
          required
          placeholder={buddy?.name}
          value={nameChange}
          InputLabelProps={{ shrink: false }}
          variant="outlined"
          inputProps={{ maxLength: MAX_LENGTH_NAME_GROUP }}
        />
      </DialogContent>
      <DialogActions>
        <LoadingButton
          loading={loading}
          role="button"
          data-testid="buttonCancel"
          tabIndex={0}
          variant="contained"
          size="medium"
          color="primary"
          onClick={handleSaveName}
          sx={{ minWidth: 120 }}
        >
          {i18n.formatMessage({ id: 'save' })}
        </LoadingButton>
        <Button
          role="button"
          data-testid="buttonSubmit"
          tabIndex={1}
          variant="outlined"
          size="medium"
          color="primary"
          sx={{ minWidth: 120 }}
          onClick={onCancel}
        >
          {i18n.formatMessage({ id: 'cancel' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

export default ChangeNameGroupDialog;
