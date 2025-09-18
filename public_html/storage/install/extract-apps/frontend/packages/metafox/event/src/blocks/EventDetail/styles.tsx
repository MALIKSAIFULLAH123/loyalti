import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        [theme.breakpoints.down('sm')]: {
          marginTop: '-4px',
          '& $viewContainer': {
            borderRadius: 0,
            marginTop: '0 !important',
            border: 'none'
          },
          '& $headingWrapper': {
            flexDirection: 'row-reverse'
          },
          '& $calendar': {
            width: 80,
            height: 80,
            marginRight: theme.spacing(0),
            marginLeft: theme.spacing(2)
          },
          '& $month': {
            height: '22px',
            lineHeight: '22px'
          },
          '& $day': {
            fontSize: 32,
            marginTop: theme.spacing(1)
          },
          '& $year': {
            fontSize: 13,
            marginTop: theme.spacing(1)
          },
          '& $heading': {
            paddingTop: theme.spacing(3)
          },
          '& $timeLocation': {
            marginTop: theme.spacing(3)
          }
        }
      },
      viewContainer: {
        width: '100%',
        marginLeft: 'auto',
        marginRight: 'auto',
        borderRadius: theme.shape.borderRadius,
        backgroundColor: theme.mixins.backgroundColor('paper'),
        border: theme.mixins.border('secondary'),
        marginTop: theme.spacing(0),
        padding: theme.spacing(0, 2, 0.5, 2)
      },
      headingWrapper: {
        display: 'flex'
      },
      calendar: {
        width: 120,
        height: 120,
        boxShadow: '1.4px 1.4px 6px 0 rgba(0, 0, 0, 0.1)',
        marginRight: theme.spacing(2),
        fontWeight: 'bold',
        textAlign: 'center',
        borderBottomRightRadius: theme.shape.borderRadius / 2,
        borderBottomLeftRadius: theme.shape.borderRadius / 2,
        ...(theme.palette.mode === 'dark' && {
          background: `${theme.palette.grey['700']} !important`
        })
      },
      month: {
        height: '32px',
        lineHeight: '32px',
        background: theme.palette.primary.main,
        color: '#fff'
      },
      day: {
        fontSize: 40,
        lineHeight: 0.7,
        color:
          theme.palette.mode === 'dark'
            ? theme.palette.text.primary
            : theme.palette.primary.main,
        marginTop: theme.spacing(2)
      },
      year: {
        fontSize: 18,
        lineHeight: 1,
        color:
          theme.palette.mode === 'dark'
            ? theme.palette.text.primary
            : theme.palette.primary.main,
        marginTop: theme.spacing(1.5)
      },
      heading: {
        flex: 1,
        minWidth: 0,
        paddingTop: theme.spacing(2)
      },
      itemTitle: {
        fontSize: theme.spacing(3),
        lineHeight: 1,
        fontWeight: theme.typography.fontWeightBold
      },
      itemFlag: {
        display: 'inline-flex',
        justifyContent: 'center',
        verticalAlign: 'bottom',
        '& .MuiFlag-itemView': {
          padding: theme.spacing(0.5, 0)
        }
      },
      categories: {
        display: 'flex',
        flexWrap: 'wrap',
        '& > div': {
          lineHeight: theme.spacing(2)
        },
        '& > a': {
          margin: `${theme.spacing(1)}px ${theme.spacing(1)}px ${theme.spacing(
            1
          )}px 0`
        }
      },
      timeLocation: {
        fontSize: 18,
        lineHeight: 1.2,
        color:
          theme.palette.mode === 'dark'
            ? theme.palette.text.secondary
            : theme.palette.text.hint,
        marginTop: theme.spacing(2),
        '& .ico': {
          fontSize: 20,
          marginRight: theme.spacing(1.5)
        }
      },
      time: {
        display: 'flex'
      },
      location: {
        marginTop: theme.spacing(1.5)
      },
      actionMenu: {
        border: '1px solid',
        width: '40px',
        height: '40px',
        borderRadius: theme.spacing(0.5),
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.primary.main,
        justifyContent: 'center',
        '& .ico': {
          color: theme.palette.primary.main
        }
      },
      iconButton: {
        flex: 1,
        minWidth: 0,
        marginRight: theme.spacing(1),
        border: '1px solid',
        height: '40px',
        borderRadius: theme.spacing(0.5),
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.primary,
        justifyContent: 'center',
        fontSize: theme.mixins.pxToRem(18),
        fontWeight: theme.typography.fontWeightBold,
        padding: theme.spacing(0, 2),
        '& .ico': {
          fontSize: theme.mixins.pxToRem(18),
          marginRight: theme.spacing(1)
        }
      },
      activeIconButton: {
        color: theme.palette.primary.main
      },
      itemContent: {
        fontSize: 15,
        lineHeight: 1.33,
        marginTop: theme.spacing(3),
        marginBottom: theme.spacing(5),
        '& p + p': {
          marginBottom: theme.spacing(2.5)
        }
      },
      eventIsEnd: {
        backgroundColor: `${theme.palette.background.default} !important`,
        color: `${theme.palette.text.hint} !important`,
        minWidth: 0,
        '&.Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: `${theme.palette.text.disabled} !important`
          })
        }
      },
      routeLink: {
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap'
      },
      onlineLink: {
        marginTop: theme.spacing(1.5),
        display: 'flex'
      }
    }),
  { name: 'MuiEventViewDetail' }
);

export default useStyles;
