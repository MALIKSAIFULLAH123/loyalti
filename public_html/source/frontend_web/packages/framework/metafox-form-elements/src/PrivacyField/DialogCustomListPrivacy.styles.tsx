import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      shareWith: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold,
        marginBottom: theme.spacing(1)
      },
      linkContainer: {
        marginTop: theme.spacing(1),
        display: 'flex',
        alignItems: 'center'
      }
    }),
  { name: 'DialogCustomListPrivacy' }
);
