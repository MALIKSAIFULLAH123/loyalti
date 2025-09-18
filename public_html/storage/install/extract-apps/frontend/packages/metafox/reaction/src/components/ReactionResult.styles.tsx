import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'inline-flex',
        alignItems: 'center'
      },
      listReaction: {
        display: 'inline-flex'
      },
      md: {
        '& $itemReaction': {
          width: '24px',
          height: '24px',
          '& + $itemReaction': {
            marginLeft: '-4px'
          }
        },
        '& $listReaction': {
          marginRight: theme.spacing(1.5)
        }
      },
      sm: {
        '& $itemReaction': {
          width: 16,
          height: 16,
          '& + $itemReaction': {
            marginLeft: '-3px'
          }
        },
        '& $listReaction': {
          marginRight: theme.spacing(0.25)
        }
      },
      itemReaction: {
        display: 'inline-flex',
        boxSizing: 'content-box',
        border: '1px solid',
        borderColor: theme.palette.background.paper,
        borderRadius: '100%',
        lineHeight: 0,
        width: '100%',
        height: '100%',
        '& img': {
          width: '100%',
          height: '100%',
          borderRadius: '100%'
        }
      },
      totalReaction: {
        fontSize: '13px',
        color: theme.palette.text.secondary,
        marginLeft: theme.spacing(0.5),
        '&:hover': {
          textDecoration: 'underline'
        }
      }
    }),
  { name: 'MuiReactionResult' }
);
