/**
 * @type: dialog
 * name: photo.dialog.androidSupportChoosePhoto
 */
import { useGlobal } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import React from 'react';
import { Button, Box } from '@mui/material';

export default function AddPhotoAlbum({ id }) {
  const { useDialog, i18n } = useGlobal();
  const { setDialogValue, dialogProps } = useDialog();

  return (
    <Dialog
      {...dialogProps}
      fullWidth
      maxWidth="xs"
      data-testid="androidSupportChoosePhoto"
    >
      <DialogTitle>{i18n.formatMessage({ id: 'choose' })}</DialogTitle>
      <DialogContent>
        <Box
          sx={{
            display: 'flex',
            flexDirection: 'column',
            '& > button:not(:last-of-type)': {
              marginBottom: theme => theme.spacing(2)
            }
          }}
        >
          <Button
            role="button"
            autoFocus
            variant="contained"
            disableRipple
            size="medium"
            color="primary"
            onClick={() => {
              setDialogValue('photo');
            }}
          >
            {i18n.formatMessage({ id: 'choose_from_library' })}
          </Button>
          <Button
            role="button"
            autoFocus
            variant="outlined"
            disableRipple
            size="medium"
            color="primary"
            onClick={() => {
              setDialogValue('camera');
            }}
          >
            {i18n.formatMessage({ id: 'take_a_photo' })}
          </Button>
        </Box>
      </DialogContent>
    </Dialog>
  );
}
