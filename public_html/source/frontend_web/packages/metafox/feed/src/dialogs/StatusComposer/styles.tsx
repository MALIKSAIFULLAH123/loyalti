import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        // fix: When composing a long text post, it should not show 2 scroll bars as capture.
        maxHeight: 'unset !important',
        display: 'flex',
        flexDirection: 'column',
        padding: '0 !important',
        [theme.breakpoints.down('sm')]: {
          width: 'unset'
        }
      },
      infoWrapper: {
        padding: theme.spacing(2.5, 2, 2, 2),
        display: 'flex',
        alignItems: 'center'
      },
      userName: {
        marginBottom: theme.spacing(0.75),
        maxWidth: 360
      },
      buttonWrapper: {
        display: 'flex'
      },
      titleDialog: {
        display: 'flex',
        alignItems: 'center'
      },
      title: {
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold
      },
      btnBack: {
        paddingRight: `${theme.spacing(2)} !important`,
        [theme.breakpoints.down('sm')]: {
          paddingRight: 16
        }
      },
      closeDialog: {
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        marginLeft: 'auto',
        cursor: 'pointer'
      },
      parentInfo: {
        backgroundColor:
          theme.palette.mode === 'light'
            ? theme.palette.grey['300']
            : theme.palette.grey['600'],
        height: 32,
        border: theme.mixins.border('secondary'),
        borderRadius: theme.spacing(0.5),
        fontWeight: theme.typography.fontWeightBold,
        display: 'flex',
        alignItems: 'center',
        padding: theme.spacing(0, 1.5, 0, 1.5),
        fontSize: theme.mixins.pxToRem(13),
        maxWidth: 200
      },
      parentName: {
        fontSize: theme.mixins.pxToRem(13),
        fontWeight: theme.typography.fontWeightBold
      },
      composeWrapper: {
        display: 'flex',
        alignItems: 'flex-start',
        padding: theme.spacing(0, 0, 0, 2)
      },
      avatarStage: {
        width: 'fit-content'
      },
      composeInner: {
        flexGrow: 1,
        minWidth: 0,
        display: 'flex',
        alignItems: 'flex-start'
      },
      dialogActions: {
        marginTop: '0 !important'
      },
      composer: {
        flexGrow: 1,
        minWidth: 0,
        flexBasis: 'auto',
        position: 'relative',
        fontWeight: theme.typography.fontWeightRegular,
        fontSize: theme.mixins.pxToRem(15),
        wordBreak: 'break-word',
        wordWrap: 'break-word',
        '&.withBackgroundStatus': {
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          '& .editor-mention': {
            background: 'rgba(0,0,0,0.1)'
          }
        },
        '& .mentionText': {
          color: theme.palette.primary.main
        },
        '& .hashtagStyle': {
          color: theme.palette.primary.main
        },
        [theme.breakpoints.down('sm')]: {
          fontSize: `${theme.mixins.pxToRem(15)} !important`,
          '&.withBackgroundStatus': {
            minHeight: '200px !important'
          }
        }
      },
      composeTextArea: {
        overflowX: 'hidden',
        minHeight: 150,
        outline: 'none',
        '&:empty::before': {
          color: theme.palette.text.secondary,
          content: 'attr(placeholder)',
          fontSize: theme.mixins.pxToRem(15),
          pointerEvents: 'none'
        }
      },
      editorComponentsWrapper: {
        position: 'fixed',
        zIndex: theme.zIndex.speedDial,
        '& .mentionSuggestionsWrapper': {
          minWidth: '300px',
          position: 'absolute',
          zIndex: 1,
          backgroundColor: theme.mixins.backgroundColor('paper'),
          borderRadius: theme.shape.borderRadius / 2,
          padding: theme.spacing(1),
          marginTop: theme.spacing(1),
          boxShadow:
            '0px 10px 13px -6px rgba(0,0,0,0.2), 0px 20px 31px 3px rgba(0,0,0,0.14)'
        }
      },
      contentWrapper: {
        display: 'flex',
        flexFlow: 'wrap',
        '&.withBackgroundStatus': {
          flexDirection: 'column'
        }
      },
      attachIconsWrapper: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: theme.spacing(0, 2),
        marginLeft: 'auto',
        flexDirection: 'column'
      },
      btnShare: {
        fontSize: `${theme.mixins.pxToRem(15)} !important`,
        width: 160,
        height: 40,
        '&.Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: `${theme.palette.text.hint} !important`
          })
        },
        [theme.breakpoints.down('sm')]: {
          width: 'auto',
          height: 32
        }
      },
      tagsStage: {
        color: theme.palette.text.secondary,
        padding: theme.spacing(2),
        fontSize: theme.typography.body1.fontSize,
        '&:empty': {
          display: 'none'
        },
        '& b': {
          color: theme.palette.text.primary
        }
      },
      loading: {
        position: 'relative',
        margin: theme.spacing(1, 2, 2, 2),
        borderRadius: theme.shape.borderRadius,
        borderWidth: 'thin',
        borderStyle: 'solid',
        borderColor: theme.palette.border.secondary,
        padding: theme.spacing(2, 3),
        height: theme.spacing(10),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        '& svg': {
          color: theme.palette.border.secondary
        }
      },
      attachmentStage: {}
    }),
  {
    name: 'StatusComposerDialog'
  }
);

export default useStyles;
