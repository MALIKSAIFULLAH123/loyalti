import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        backgroundColor: theme.palette.background.paper,
        padding: theme.spacing(2),
        paddingBottom: 0,
        [theme.breakpoints.down('sm')]: {
          borderRadius: 0
        }
      },
      rootSkeleton: {
        padding: theme.spacing(2)
      },
      header: {
        display: 'flex',
        flexDirection: 'row',
        marginBottom: theme.spacing(1.5),
        '& button:hover': {
          backgroundColor: theme.palette.action.selected
        }
      },
      headerInfo: {
        flex: 1
      },
      headerHeadline: {
        fontSize: theme.mixins.pxToRem(15),
        '& a:not(.simpleLink)': {
          fontWeight: theme.typography.fontWeightBold,
          color: theme.palette.text.primary
        },
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
      headerActionMenu: {
        '& .ico': {
          fontSize: theme.mixins.pxToRem(18),
          color: theme.palette.text.secondary
        }
      },
      profileLink: {
        fontWeight: theme.typography.fontWeightBold
      },
      locationLink: {
        fontWeight: theme.typography.fontWeightBold
      },
      caretIcon: {
        color: theme.palette.text.secondary,
        display: 'inline-block',
        transform: 'translateY(2px) translateX(1px)'
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
        '& span:first-of-type + span:before': {
          content: '"·"',
          display: 'inline-block',
          padding: `${theme.spacing(0, 0.5)}`
        },
        '& button': {
          '& span + span:before': {
            content: '"" !important',
            display: 'inline-block',
            color: 'red',
            padding: '0 !important'
          }
        },
        '& button.MuiButton-root': {
          minWidth: '0 !important',
          padding: '4px !important',
          marginLeft: '-4px !important',
          background: 'transparent',
          color: `${theme.palette.text.secondary} !important`,
          '& span.MuiButton-startIcon': {
            marginLeft: '0px !important'
          },
          '& span.MuiButton-endIcon': {
            margin: '0px !important'
          }
        }
      },
      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(2),
        wordBreak: 'break-word',
        '& .profileLink, & .profileLink:active': {
          color: theme.palette.primary.main
        },
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
        margin: `0 ${theme.spacing(-2)}`,
        '&:before': {
          content: '""',
          display: 'block',
          paddingBottom: '56.25%'
        },
        [theme.breakpoints.down('sm')]: {
          overflow: 'hidden'
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
        paddingLeft: theme.spacing(9),
        paddingRight: theme.spacing(9),
        paddingBottom: theme.spacing(4.5),
        paddingTop: theme.spacing(4.5),
        margin: 0,
        fontSize: theme.mixins.pxToRem(28),
        lineHeight: '37px',
        minHeight: '109px',
        fontWeight: 'bold',
        color: '#fff',
        '& p:empty': {
          margin: 0
        },
        '& a': {
          color: '#fff !important'
        },
        [theme.breakpoints.down('sm')]: {
          fontSize: theme.mixins.pxToRem(20),
          lineHeight: '30px',
          padding: theme.spacing(2.5, 2)
        }
      },
      actionButtonStaticsWrapper: {
        minHeight: theme.spacing(2),
        display: 'flex',
        alignItems: 'center',
        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column-reverse'
        }
      },
      hiddenFeed: {
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.primary,
        paddingBottom: theme.spacing(2),
        justifyContent: 'space-between'
      },
      hiddenIcon: {
        fontSize: 24
      },
      hiddenText: {
        display: 'flex',
        alignItems: 'center'
      },
      hiddenDescription: {
        paddingLeft: theme.spacing(2)
      },
      hiddenTitle: {
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: 'bold',
        marginBottom: theme.spacing(0.5)
      },
      hiddenSubtitle: {
        fontSize: theme.mixins.pxToRem(13)
      },
      contentSkeleton: {
        paddingBottom: theme.spacing(2)
      }
    }),
  { name: 'FeedItemView' }
);
