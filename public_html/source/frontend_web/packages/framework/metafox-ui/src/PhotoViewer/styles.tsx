import { makeStyles, createStyles } from '@mui/styles';
import { Theme } from '@mui/material';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      previewWrapper: {
        position: 'relative',
        overflow: 'hidden',
        display: 'flex'
      },
      previewZoom: {
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        position: 'absolute',
        top: 0,
        bottom: 0,
        left: 0,
        right: 0,
        zIndex: 2
      },
      imgPreview: {
        visibility: 'hidden',
        maxHeight: '80vh'
      }
    }),
  { name: 'PhotoViewer' }
);

export default useStyles;
