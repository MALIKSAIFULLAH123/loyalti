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
        overflow: 'hidden',
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
        width: theme.spacing(25),
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
        minWidth: 0,
        '& p': {
          wordBreak: 'break-word',
          margin: 0
        }
      },
      itemButton: {
        display: 'flex',
        justifyContent: 'flex-end',
        minWidth: theme.spacing(10),
        [theme.breakpoints.down('sm')]: {
          width: 'unset'
        }
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
      itemText: {
        display: 'flex',
        flex: 1,
        width: '100%',
        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column'
        },
        marginRight: theme.spacing(1)
      }
    }),
  { name: 'AccountSetting' }
);
