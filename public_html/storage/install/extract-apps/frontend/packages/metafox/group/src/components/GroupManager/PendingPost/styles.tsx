import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        backgroundColor: theme.palette.background.paper,
        padding: theme.spacing(2)
      },
      header: {
        display: 'flex',
        flexDirection: 'row',
        marginBottom: theme.spacing(2)
      },
      headerInfo: {
        padding: '4px 0',
        flex: 1
      },
      headerHeadline: {
        fontSize: '0.9375rem',
        '& a': {
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
      caretIcon: {
        color: theme.palette.text.secondary
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
      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(2)
      },
      statusBgWrapper: {
        display: 'block',
        margin: 0,
        position: 'relative',
        backgroundSize: 'cover',
        backgroundPosition: 'center center',
        backgroundRepeat: 'no-repeat',
        backgroundOrigin: 'border-box',
        border: '1px solid rgba(0,0,0,0.1)',
        width: 'auto',
        marginBottom: theme.spacing(2),
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
        paddingLeft: theme.spacing(9),
        paddingRight: theme.spacing(9),
        paddingBottom: theme.spacing(4.5),
        paddingTop: theme.spacing(4.5),
        margin: 0,
        fontSize: '28px',
        lineHeight: '37px',
        minHeight: '109px',
        fontWeight: 'bold',
        color: '#fff',
        '& p:empty': {
          margin: 0
        },
        '& a': {
          color: '#fff !important'
        }
      },
      actionButtonStaticsWrapper: {
        display: 'flex',
        alignItems: 'center',
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary
      },

      hiddenFeed: {
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.primary,
        paddingBottom: theme.spacing(2),
        '& .ico': {
          fontSize: 24,
          marginRight: theme.spacing(2)
        },
        '& button': {
          marginLeft: 'auto',
          borderRadius: 4,
          fontWeight: 'bold',
          borderColor: theme.palette.text.primary
        }
      },
      hiddenTitle: {
        fontSize: theme.mixins.pxToRem(15),
        fontWeight: 'bold',
        marginBottom: theme.spacing(0.5)
      },
      hiddenText: {
        fontSize: theme.mixins.pxToRem(13)
      },
      divider: {
        border: '1px solid #ededed',
        margin: theme.spacing(1, 0)
      },
      controlBtnWrapper: {
        display: 'flex'
      },
      controlBtn: {
        borderRadius: 8,
        marginRight: theme.spacing(1)
      }
    }),
  { name: 'PendingPost' }
);
