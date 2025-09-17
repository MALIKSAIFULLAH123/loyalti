import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        backgroundColor: theme.palette.background.paper,
        borderRadius: theme.shape.borderRadius,
        transition: 'all 0.2s ease',
        position: 'relative',
        height: '100%',
        cursor: 'pointer',
        border: theme.mixins.border('secondary'),
        '&:hover': {
          opacity: 0.8
        }
      },
      outer: {
        position: 'relative'
      },
      inner: {
        position: 'relative',
        padding: theme.spacing(2),
        height: 100
      },
      mediaContent: {
        position: 'relative'
      },
      totalPhoto: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        lineHeight: '16px',
        display: 'flex'
      },
      media: {
        display: 'block',
        position: 'relative',
        borderTopLeftRadius: theme.shape.borderRadius,
        borderTopRightRadius: theme.shape.borderRadius,
        overflow: 'hidden',
        height: 122
      },
      owner: {
        fontSize: theme.mixins.pxToRem(13),
        lineHeight: '16px',
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(1),
        marginBottom: theme.spacing(1),
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        display: 'block'
      }
    }),
  { name: 'MuiPhotoAlbumCoverItemView' }
);

export default useStyles;
