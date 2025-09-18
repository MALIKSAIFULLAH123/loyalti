import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      paper: {
        width: 372,
        overflow: 'hidden',
        padding: theme.spacing(2),
        boxShadow: theme.shadows[12]
      },
      userHeader: {
        display: 'flex',
        alignItems: 'center'
      },
      userAvatar: {
        width: 80,
        height: 80
      },
      description: {
        marginTop: theme.spacing(1.5)
      },
      userName: {
        fontSize: theme.typography.body1.fontSize,
        lineHeight: 2.41,
        fontWeight: 'bold',
        marginLeft: theme.spacing(1.5),
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        maxWidth: '100%',
        '& a': {
          color: theme.palette.text.primary
        }
      },
      statistic: {
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(1),
        '& span:first-of-type': {
          marginRight: '0.4em'
        }
      },
      buttonWrapper: {
        display: 'flex',
        margin: theme.spacing(1.5, -0.5, 0)
      },
      button: {
        flex: 1
      },
      liked: {
        backgroundColor: theme.palette.action.selected,
        color: theme.palette.text.primary,
        '&:hover': {
          backgroundColor: theme.palette.background.default
        }
      }
    }),
  { name: 'MuiPageProfilePopup' }
);

export default useStyles;
