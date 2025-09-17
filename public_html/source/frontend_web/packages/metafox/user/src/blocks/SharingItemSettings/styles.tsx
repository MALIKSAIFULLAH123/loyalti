import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      listItem: {
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary,
        padding: '22px 0 !important',
        display: 'flex',
        justifyContent: 'space-between',
        '&:first-of-type': {
          paddingTop: 6
        },
        '&:last-child': {
          paddingBottom: 6,
          borderBottom: 'none'
        }
      },
      controlItem: {}
    }),
  { name: 'SharingItemSetting' }
);
