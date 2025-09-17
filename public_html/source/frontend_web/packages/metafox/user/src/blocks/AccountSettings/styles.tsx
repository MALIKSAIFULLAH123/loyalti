import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      item: {
        display: 'flex',
        alignItems: 'center',
        padding: '22px 0',
        justifyContent: 'space-between',
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary,
        '&:first-of-type': {
          paddingTop: 6
        },
        '&:last-child': {
          paddingBottom: 6,
          borderBottom: 'none'
        }
      },
      itemTitle: {
        width: 210,
        fontSize: theme.typography.body1.fontSize,
        lineHeight: 1.6,
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.text.primary
      },
      itemContentInner: {
        display: 'flex',
        flexDirection: 'column',
        [theme.breakpoints.down('sm')]: {
          paddingTop: theme.spacing(1)
        }
      },
      itemContent: {
        fontSize: theme.typography.body1.fontSize,
        lineHeight: 1.6,
        color: theme.palette.text.primary,
        flex: 1,
        '& .MuiFormControl-root': {
          padding: theme.spacing(0.5, 0)
        }
      },
      itemButton: {
        display: 'flex',
        justifyContent: 'flex-end',
        width: 200
      },
      btnEdit: {
        textTransform: 'none',
        fontWeight: theme.typography.fontWeightBold
      },
      inputEdit: {
        width: 300,
        '& + $inputEdit': {
          marginTop: theme.spacing(2)
        }
      },
      selectEdit: {
        width: 300,
        height: 40
      },
      itemText: {
        display: 'flex',
        flex: 1,
        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column'
        }
      }
    }),
  { name: 'AccountSetting' }
);
