import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      image: {
        maxWidth: '100%',
        maxHeight: '100vh',
        float: 'left',
        opacity: 0,
        [theme.breakpoints.down('sm')]: {
          maxHeight: '350px'
        }
      },
      imageHeightAuto: {
        maxHeight: 'initial',
        [theme.breakpoints.down('sm')]: {
          maxHeight: '350px'
        }
      },
      visibleImage: {
        opacity: 1
      },
      boxFake: {
        position: 'absolute',
        top: 0,
        right: 0,
        left: 0,
        objectFit: 'cover',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      },
      imageFake: {
        width: '100%',
        height: 'auto'
      },
      actionBar: {
        position: 'absolute',
        right: 0,
        top: 0,
        padding: theme.spacing(1),
        display: 'flex',
        justifyContent: 'flex-end',
        zIndex: 1,
        alignItems: 'center'
      },
      tagFriend: {
        color: '#fff !important',
        width: 32,
        height: 32,
        fontSize: theme.mixins.pxToRem(15)
      },
      dropDown: {
        color: '#fff',
        minWidth: '32px',
        height: '32px',
        fontSize: theme.mixins.pxToRem(15)
      },
      taggedBox: {
        position: 'absolute',
        left: 0,
        top: 0,
        width: '100px',
        height: '100px',
        border: '2px solid',
        borderColor: theme.palette.background.paper,
        boxShadow: theme.shadows[20],
        transform: 'translate(-50%,-50%)'
      },
      suggestFriend: {
        backgroundColor: theme.mixins.backgroundColor('paper'),
        marginTop: '10px',
        marginLeft: '-70px',
        border: theme.mixins.border('secondary'),
        borderRadius: theme.shape.borderRadius,
        '&  .MuiFilledInput-root': {
          background: 'none'
        }
      },
      smallAvatar: {
        width: '24px',
        height: '24px',
        marginRight: theme.spacing(1)
      },
      whoIsThis: {
        height: '100px',
        width: '100%'
      },
      popper: {
        zIndex: theme.zIndex.snackbar,
        backgroundColor: theme.mixins.backgroundColor('paper'),
        padding: theme.spacing(1),
        boxShadow: theme.shadows[20],
        borderRadius: theme.shape.borderRadius,
        margin: theme.spacing(1, 0),
        width: '240px',
        '& .MuiAutocomplete-paper': {
          boxShadow: 'none'
        },
        '& .MuiAutocomplete-listbox': {
          padding: 0
        },
        '& .MuiAutocomplete-option': {
          borderRadius: theme.shape.borderRadius,
          padding: '6px 8px'
        },
        '& .MuiAutocomplete-root': {
          maxWidth: '224px'
        },
        '& .MuiFilledInput-underline:after': {
          display: 'none'
        },
        '& .MuiAutocomplete-endAdornment': {
          display: 'none'
        },
        '& .MuiFilledInput-underline:before': {
          display: 'none'
        },
        '& .MuiFilledInput-root': {
          background: 'none',
          border: theme.mixins.border('secondary'),
          borderRadius: theme.shape.borderRadius
        },
        '& .MuiAutocomplete-noOptions': {
          padding: theme.spacing(1.5, 1)
        }
      },
      tagItem: {
        display: 'flex',
        alignItems: 'center',
        width: '100%',
        padding: theme.spacing(0.5, 1),
        textAlign: 'left',
        cursor: 'pointer',
        '&:hover': {
          background: theme.palette.action.selected,
          borderRadius: theme.shape.borderRadius / 2
        }
      },
      userName: {
        flexGrow: 1,
        whiteSpace: 'nowrap',
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        display: 'block',
        fontSize: theme.mixins.pxToRem(14)
      },
      inputBaseBox: {
        '& input': {
          width: '100%',
          border: theme.mixins.border('secondary'),
          borderRadius: theme.shape.borderRadius,
          padding: theme.spacing(1),
          fontSize: theme.mixins.pxToRem(15),
          '&:focus': {
            border: theme.mixins.border('secondary'),
            outline: 'none'
          }
        }
      },
      message: {
        color: '#fff',
        margin: theme.spacing(0, 1)
      },
      iconButton: {
        color: 'white'
      },
      clear: {
        clear: 'both'
      }
    }),
  { name: 'EditPreviewPhotoTag' }
);

export default useStyles;
