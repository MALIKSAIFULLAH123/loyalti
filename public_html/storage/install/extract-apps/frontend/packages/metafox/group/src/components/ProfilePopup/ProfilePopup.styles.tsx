import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      paper: {
        width: 372,
        overflow: 'hidden',
        boxShadow: theme.shadows[12]
      },
      cover: {
        width: '100%',
        height: 116,
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'center',
        borderTopLeftRadius: theme.shape.borderRadius,
        borderTopRightRadius: theme.shape.borderRadius
      },
      userName: {
        width: 372,
        lineHeight: 2.41,
        '& a': {
          color: theme.palette.text.primary,
          fontWeight: theme.typography.fontWeightBold
        }
      },
      link: {
        fontWeight: '600 !important'
      },
      popupInner: {
        padding: theme.spacing(2)
      },
      type: {
        marginTop: theme.spacing(1.5),
        color: theme.palette.text.secondary,
        fontSize: theme.typography.body2.fontSize,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        maxWidth: '100%',
        '& .ico': {
          fontSize: theme.mixins.pxToRem(15),
          marginTop: 1
        }
      },
      description: {
        marginTop: theme.spacing(1.5)
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
        marginTop: theme.spacing(1.5)
      }
    }),
  { name: 'MuiGroupProfilePopup' }
);

export default useStyles;
