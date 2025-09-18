import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        marginBottom: theme.spacing(1),
        '&:hover $media > div:before': {
          opacity: 1,
          background: 'rgba(0,0,0,0.8)'
        }
      },
      outer: {
        position: 'relative'
      },
      inner: {
        position: 'relative'
      },
      title: {
        marginBottom: theme.spacing(0.5)
      },
      media: {
        display: 'block',
        position: 'relative',
        borderRadius: theme.shape.borderRadius,
        overflow: 'hidden',
        width: '40px',
        height: '40px',
        border: '1px solid',
        borderColor: theme.palette.background.paper
      },
      pageShadow: {
        color: '#fff',
        display: 'flex',
        alignItems: 'flex-end',
        padding: theme.spacing(1),
        width: '100%',
        height: '100%',
        backgroundImage:
          'linear-gradient(to top, rgba(0,0,0,0.4) 0, rgba(0,0,0,0) 90%)'
      },
      pageLike: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column',
        paddingLeft: theme.spacing(1),
        paddingRight: theme.spacing(1),
        marginBottom: '2px'
      },
      mediaBg: {
        display: 'block',
        position: 'relative',
        backgroundSize: 'cover',
        backgroundPosition: 'center center',
        backgroundRepeat: 'no-repeat',
        backgroundOrigin: 'border-box',
        border: '1px solid rgba(0, 0, 0, 0.1)',
        transition: 'all 300ms ease',
        '&::before': {
          content: '""',
          display: 'block',
          paddingBottom: '53%'
        }
      },
      statistic: {
        fontSize: '12px',
        lineHeight: '16px',
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        overflow: 'hidden'
      },
      pageCover: {
        height: '72px',
        marginBottom: theme.spacing(1),
        backgroundPosition: 'center',
        backgroundSize: 'cover',
        border: '1px solid rgba(0,0,0,0.1)'
      },
      categoryName: {
        fontSize: '12px',
        color: theme.palette.text.secondary
      },
      skeleton: {
        width: '100%',
        height: '120px'
      }
    }),
  { name: 'MuiPagesItemSmallView' }
);
export default useStyles;
