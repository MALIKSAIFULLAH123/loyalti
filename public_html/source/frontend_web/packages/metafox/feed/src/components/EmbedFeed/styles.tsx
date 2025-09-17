import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        padding: theme.spacing(2)
      },
      noMarginBottom: {
        marginBottom: 0,
        paddingTop: 0,
        paddingBottom: 0,
        overflow: 'hidden'
      },
      rootSkeleton: {
        padding: theme.spacing(2)
      },
      header: {
        display: 'flex',
        flexDirection: 'row',
        marginBottom: theme.spacing(1.5)
      },
      paddingTopMedia: {
        paddingTop: theme.spacing(1.5),
        marginBottom: theme.spacing(2)
      },
      headerInfo: {
        flex: 1
      },
      headerHeadline: {
        fontSize: theme.mixins.pxToRem(15),
        '& a:hover': {
          textDecoration: 'underline'
        }
      },
      headlineSpan: {
        paddingRight: theme.spacing(0.5),
        color: theme.palette.text.secondary
      },
      headerAvatarHolder: {
        paddingRight: theme.spacing(1.5)
      },
      profileLink: {
        fontWeight: theme.typography.fontWeightBold,
        color: `${theme.palette.text.primary} !important`
      },
      privacyBlock: {
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        fontSize: '0.8125rem',
        paddingTop: '0.25em'
      },
      separateSpans: {
        display: 'flex',
        alignItems: 'center',
        '& span + span:before': {
          content: '"·"',
          display: 'inline-block',
          padding: `${theme.spacing(0, 0.5)}`
        }
      },
      caretIcon: {
        color: theme.palette.text.secondary,
        display: 'inline-block',
        transform: 'translateY(2px) translateX(1px)'
      },
      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(2),
        wordBreak: 'break-word',
        '& a': {
          color: theme.palette.primary.main,
          '&:hover': {
            textDecoration: 'underline'
          }
        }
      },
      statusBgWrapper: {
        display: 'block',
        position: 'relative',
        backgroundSize: 'cover',
        backgroundPosition: 'center center',
        backgroundRepeat: 'no-repeat',
        backgroundOrigin: 'border-box',
        border: '1px solid rgba(0,0,0,0.1)',
        width: 'auto',
        margin: `0 ${theme.spacing(-2)} ${theme.spacing(-2)}`,
        '&:before': {
          content: '""',
          display: 'block',
          paddingBottom: '56.25%'
        }
      },
      statusBgInner: {
        wordBreak: 'break-word',
        wordWrap: 'break-word',
        maxWidth: '100%',
        zIndex: 2,
        position: 'absolute',
        top: '50%',
        transform: 'translateY(-50%)',
        textAlign: 'center',
        overflow: 'hidden',
        width: '100%',
        paddingBottom: theme.spacing(4.5),
        paddingTop: theme.spacing(4.5),
        margin: 0,
        minHeight: '109px',
        fontWeight: 'bold',
        color: '#fff',
        fontSize: theme.mixins.pxToRem(28),
        lineHeight: '37px',
        paddingLeft: theme.spacing(9),
        paddingRight: theme.spacing(9),
        [theme.breakpoints.down('sm')]: {
          fontSize: theme.mixins.pxToRem(18),
          lineHeight: '30px',
          paddingLeft: theme.spacing(3),
          paddingRight: theme.spacing(3)
        },
        '& p:empty': {
          margin: 0
        },
        '& a': {
          color: '#fff !important'
        }
      }
    }),
  { name: 'MuiFeedItemView' }
);
