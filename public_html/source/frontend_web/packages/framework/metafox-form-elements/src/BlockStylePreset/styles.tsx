import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      previewSlot: {
        display: 'none'
      },
      previewSlotOpen: {
        padding: theme.spacing(2),
        height: 360,
        display: 'block',
        backgroundColor: theme.palette.action.disabled
      }
    }),
  { name: 'BlockStylePresetField' }
);
