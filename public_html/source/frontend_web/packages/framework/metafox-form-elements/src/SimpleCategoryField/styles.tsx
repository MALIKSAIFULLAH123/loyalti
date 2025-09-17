import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles((theme: Theme) =>
  createStyles({
    item: {
      '& $link': {
        height: theme.spacing(7),
        padding: theme.spacing(0, 0, 0, 2),
        flex: 1,
        minWidth: 0,
        '&:hover': {
          backgroundColor: theme.palette.action.selected,
          borderRadius: theme.shape.borderRadius
        }
      }
    },
    hasSubs: {
      display: 'flex',
      alignItems: 'center'
    },
    link: {
      
    },
    itemActive: {
      '& $link': {
        color: theme.palette.primary.main,
        fontWeight: theme.typography.fontWeightBold
      }
    },
    span: {},
    subCategory: {
      paddingLeft: theme.spacing(0),
      margin: 0,
      listStyle: 'none',
      '& span': {
        paddingLeft: theme.spacing(2)
      }
    },
    icon: {
      position: 'absolute',
      width: 32,
      height: 32,
      right: theme.spacing(1),
      '& .ico': {
        fontSize: theme.mixins.pxToRem(15)
      }
    }
  })
);
