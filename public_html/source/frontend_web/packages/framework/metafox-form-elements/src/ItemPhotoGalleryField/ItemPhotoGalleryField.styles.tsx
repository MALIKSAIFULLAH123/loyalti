import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        margin: theme.spacing(2, 0, 1),
        '& button': {
          fontSize: theme.mixins.pxToRem(13),
          border: theme.mixins.border('primary'),
          borderRadius: 4,
          padding: theme.spacing(0.5, 2)
        },
        '& .ico': {
          fontSize: theme.mixins.pxToRem(15)
        }
      },
      addPhotoWrapper: {},
      previewList: {
        display: 'flex',
        margin: theme.spacing(1, -0.5, 0)
      },
      previewItem: {
        margin: theme.spacing(0, 0.5),
        position: 'relative',
        '& img': {
          width: 48,
          height: 48,
          minWidth: 48,
          minHeight: 48,
          objectFit: 'cover',
          borderRadius: 4
        }
      },
      deletePhoto: {
        position: 'absolute',
        top: 4,
        right: 4,
        borderRadius: '50%',
        width: 16,
        height: 16,
        opacity: 0.7,
        backgroundColor: theme.mixins.backgroundColor('paper'),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: '8px !important',
        cursor: 'pointer',
        '&:hover': {
          opacity: 1
        }
      },
      totalPhoto: {
        marginTop: theme.spacing(0.5)
      },
      controls: {
        height: 120,
        border: 'solid 1px rgba(0, 0, 0, 0.23)',
        borderRadius: 4,
        padding: theme.spacing(0.5, 2),
        marginTop: theme.spacing(1),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        '&:hover': {
          border: '1px solid',
          borderColor: theme.palette.border.primary
        }
      }
    }),
  {
    name: 'ItemPhotoGalleryField'
  }
);
