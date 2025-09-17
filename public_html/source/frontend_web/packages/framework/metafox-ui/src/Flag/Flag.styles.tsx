import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      item: {
        display: 'inline-flex',
        alignItems: 'center',
        fontSize: '13px'
      },
      primary: {
        color:
          theme.palette.mode === 'light'
            ? theme.palette.primary.dark
            : theme.palette.primary.light
      },
      white: {
        color: theme.palette.common.white
      },
      yellow: {
        color:
          theme.palette.mode === 'light'
            ? theme.palette.warning.main
            : theme.palette.warning.light
      },
      icon: {
        display: 'inline-flex',
        marginRight: theme.spacing(1),
        fontSize: '13px'
      },
      text: {
        fontWeight: theme.typography.fontWeightBold
      },
      itemView: {
        marginBottom: theme.spacing(0.5),
        '& .MuiFlag-icon': {
          display: 'none'
        },
        '& $text': {
          backgroundColor: theme.palette.primary.dark,
          paddingLeft: theme.spacing(1),
          paddingRight: theme.spacing(1),
          height: theme.spacing(3),
          lineHeight: '24px',
          fontWeight: theme.typography.fontWeightRegular,
          position: 'relative',
          '&.shadow:after': {
            content: '""',
            width: 0,
            height: 0,
            filter: 'drop-shadow(1px 1px 1px rgba(0,0,0,.5))',
            borderColor:
              'rgba(0, 0, 0, 0.3) transparent transparent transparent',
            borderStyle: 'solid',
            borderWidth: '3px 2px 0 0',
            WebkitFilter: 'drop-shadow(1px 1px 1px rgba(0,0,0,.5))',
            position: 'absolute',
            right: 0,
            bottom: -3
          },
          '&$is_expires': {
            backgroundColor: theme.palette.error.main
          },
          '&$is_denied': {
            backgroundColor: theme.palette.error.main
          },
          '&$is_sponsor': {
            backgroundColor: theme.palette.warning.main
          },
          '&$is_pending': {
            backgroundColor: theme.palette.primary.light
          }
        }
      },
      detailView: {
        margin: theme.spacing(0.25, 0.5, 0),
        display: 'inline-flex',
        '& .MuiFlag-icon': {
          display: 'none'
        },
        '& $text': {
          backgroundColor: theme.palette.primary.dark,
          paddingLeft: theme.spacing(1),
          paddingRight: theme.spacing(1),
          height: theme.spacing(3),
          lineHeight: '24px',
          fontWeight: theme.typography.fontWeightRegular,
          position: 'relative',
          '&.shadow:after': {
            content: '""',
            width: 0,
            height: 0,
            filter: 'drop-shadow(1px 1px 1px rgba(0,0,0,.5))',
            borderColor:
              'rgba(0, 0, 0, 0.3) transparent transparent transparent',
            borderStyle: 'solid',
            borderWidth: '3px 2px 0 0',
            WebkitFilter: 'drop-shadow(1px 1px 1px rgba(0,0,0,.5))',
            position: 'absolute',
            right: 0,
            bottom: -3
          },
          '&$is_sponsor': {
            backgroundColor: theme.palette.warning.main
          },
          '&$is_pending': {
            backgroundColor: theme.palette.primary.light
          },
          '&$is_expires': {
            backgroundColor: theme.palette.error.main
          },
          '&$is_denied': {
            backgroundColor: theme.palette.error.main
          }
        }
      },
      is_sponsor: {},
      is_pending: {},
      is_expires: {},
      is_denied: {}
    }),
  {
    name: 'MuiFlag'
  }
);
