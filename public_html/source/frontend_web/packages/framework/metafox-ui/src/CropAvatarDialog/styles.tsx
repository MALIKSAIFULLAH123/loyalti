import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      avatarWrapper: {
        marginTop: -96,
        marginRight: theme.spacing(3),
        position: 'relative'
      },
      userAvatar: {
        width: 168,
        height: 168,
        border: '4px solid',
        borderColor: theme.palette.background.paper
      },
      btnClose: {
        background: 'none',
        boxShadow: 'none',
        padding: 0,
        fontSize: theme.mixins.pxToRem(18),
        minWidth: '1px',
        color: `${theme.palette.text.secondary} !important`,
        marginRight: `${theme.spacing(-1)} !important`,
        marginLeft: 'auto !important',
        width: theme.spacing(4),
        height: theme.spacing(4),
        borderRadius: theme.spacing(4),
        '&:hover': {
          background: theme.palette.background.default,
          color: '#fff'
        }
      },
      btnControl: {
        background: 'none',
        boxShadow: 'none',
        padding: 0,
        fontSize: theme.mixins.pxToRem(18),
        minWidth: theme.spacing(4),
        color: `${theme.palette.text.primary} !important`,
        width: theme.spacing(4),
        height: theme.spacing(4),
        borderRadius: theme.spacing(4),
        '&:hover': {
          background: 'rgba(5, 5, 5, 0.04)',
          color: theme.palette.text.primary,
          boxShadow: 'none'
        }
      },
      btnEditAvatar: {
        backgroundColor: theme.palette.background.paper,
        textTransform: 'capitalize',
        position: 'absolute',
        top: theme.spacing(1),
        right: theme.spacing(1),
        minWidth: 32,
        height: 32,
        borderRadius: '100%',
        '&:hover': {
          backgroundColor: theme.palette.background.default
        }
      },
      sliderZoom: {
        margin: theme.spacing(0, 2)
      },
      iconEdit: {},
      cropDialog: {
        '& .MuiDialog-paper': {
          overflowY: 'visible'
        }
      },
      titleDialog: {
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(18),
        padding: theme.spacing(2),
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      },
      cropContainer: {
        [theme.breakpoints.down('sm')]: {
          width: '100%'
        }
      },
      cropContent: {
        position: 'relative',
        height: 400,
        '& .reactEasyCrop_CropArea': {
          border: 'none',
          color: 'rgba(255,255,255,.3)'
        }
      },
      controls: {
        padding: theme.spacing(1),
        display: 'flex'
      },
      btnActions: {
        padding: theme.spacing(1),
        display: 'flex',
        justifyContent: 'flex-end',
        '& .Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: `${theme.palette.text.hint} !important`
          })
        }
      },
      btnAction: {
        margin: `${theme.spacing(1)} !important`
      },
      btnContainer: {
        padding: theme.spacing(0, 1),
        display: 'flex',
        flexGrow: 1,
        alignItems: 'center',
        maxWidth: 500,
        margin: 'auto',
        justifyContent: 'center',
        marginBottom: theme.spacing(2),
        '& button': {
          padding: theme.spacing(1, 2),
          margin: theme.spacing(0, 1),
          whiteSpace: 'nowrap',
          fontWeight: 'normal',
          color: theme.palette.text.primary
        }
      },
      sliderContainer: {
        padding: theme.spacing(0, 1),
        display: 'flex',
        flexGrow: 1,
        alignItems: 'center',
        maxWidth: 400,
        margin: 'auto'
      },
      sliderLabel: {
        '& + span': {
          marginLeft: theme.spacing(2)
        }
      },
      btnChangePhoto: {
        position: 'relative',
        overflow: 'hidden'
      },
      inputFile: {
        position: 'absolute',
        opacity: 0,
        left: 0,
        top: 0,
        right: 0,
        bottom: 0,
        cursor: 'pointer'
      },
      cropBackdrop: {
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        display: 'flex',
        overflow: 'hidden',
        position: 'absolute',
        textAlign: 'center',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: theme.mixins.backgroundColor('paper')
      },
      cropBlurContent: {
        filter: 'blur(10px)',
        transform: 'scale(12)'
      },
      blurImage: {
        width: '100%',
        height: '100%',
        objectFit: 'cover',
        border: 0
      }
    }),
  { name: 'MuiUserAvatarPhoto' }
);
