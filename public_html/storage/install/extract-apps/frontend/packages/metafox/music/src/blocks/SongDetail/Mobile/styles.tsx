import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      bgCoverWrapper: {
        minHeight: '388px',
        overflow: 'hidden',
        position: 'relative'
      },
      bgCover: {
        height: '100%',
        backgroundImage: 'linear-gradient(to top, #aeacac, #121212)',
        position: 'absolute',
        left: 0,
        right: 0,
        top: 0,
        bottom: 0,
        zIndex: -2
      },
      hasBgCover: {},
      header: {
        width: '100%',
        marginLeft: 'auto',
        marginRight: 'auto',
        color: '#fff',
        paddingTop: theme.spacing(3),
        '& .rhap_container': {
          backgroundColor: 'unset',
          boxShadow: 'none'
        }
      },
      headerInner: {},
      imgSong: {
        width: 160,
        height: 160,
        margin: theme.spacing(3, 'auto')
      },
      audioPlayer: {
        margin: theme.spacing(0, 'auto', 3),
        display: 'block',
        boxShadow: 'none',
        outline: 'none',
        '& .rhap_progress-section': {
          flexWrap: 'wrap'
        },
        '& .rhap_progress-container': {
          width: '100%',
          flex: 'none',
          margin: theme.spacing(0, 0, 2),
          outline: 'none',
          order: 1,
          '& .rhap_progress-bar-show-download': {
            backgroundColor: theme.mixins.backgroundColor('paper'),
            height: 2,
            '& .rhap_progress-filled': {
              backgroundColor: theme.palette.primary.main
            },
            '& .rhap_progress-indicator': {
              display: 'none'
            }
          }
        },
        '& .rhap_current-time': {
          order: 2,
          color: '#fff'
        },
        '& .rhap_total-time': {
          marginLeft: 'auto',
          color: '#fff',
          order: 3
        },
        '& .rhap_controls-section': {
          padding: theme.spacing(0, 2),
          alignItems: 'baseline',
          flex: 'none',
          '& .rhap_main-controls': {
            flex: 'none',
            '& button': {
              fontSize: 32,
              width: 32,
              height: 32,
              color: '#fff',
              outline: 'none',
              overflow: 'inherit',
              '&.rhap_play-pause-button': {
                margin: theme.spacing(1, 5, 0)
              }
            }
          },
          '& .rhap_additional-controls': {
            flex: 'none',
            '& button': {
              margin: theme.spacing(0, 0, 0, 'auto'),
              fontSize: 18,
              width: 18,
              height: 18,
              color: '#fff',
              outline: 'none'
            }
          }
        }
      },
      btnRepeatOn: {
        color: theme.palette.primary.main
      },
      btnControlAudio: {
        fontSize: 18,
        '&.ico-shuffle': {
          display: 'flex',
          alignItems: 'center',
          cursor: 'pointer'
        }
      },
      btnDisable: {
        color: theme.palette.text.disabled,
        cursor: 'not-allowed !important'
      },
      viewContainer: {
        width: '100%',
        maxWidth: 720,
        marginLeft: 'auto',
        marginRight: 'auto',
        backgroundColor: theme.mixins.backgroundColor('paper'),
        border: theme.mixins.border('secondary'),
        padding: theme.spacing(2),
        position: 'relative'
      },
      actionMenu: {
        width: 32,
        height: 32,
        position: 'absolute !important',
        top: theme.spacing(1),
        right: theme.spacing(1),
        '& .ico': {
          color: theme.palette.text.secondary,
          fontSize: 13
        }
      },
      titleWrapper: {
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center'
      },
      pageTitle: {
        fontWeight: theme.typography.fontWeightBold,
        flex: 1,
        minWidth: 0,
        fontSize: 18,
        lineHeight: 1,
        maxHeight: 72,
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        wordBreak: 'break-word',
        wordWrap: 'break-word',
        whiteSpace: 'normal',
        WebkitLineClamp: 3,
        display: '-webkit-box',
        WebkitBoxOrient: 'vertical',
        textAlign: 'center',
        padding: theme.spacing(0, 2)
      },
      itemFlag: {
        marginBottom: theme.spacing(2)
      },
      minorInfo: {
        marginTop: theme.spacing(2),
        color: theme.palette.text.secondary,
        fontSize: 13,
        fontWeight: theme.typography.fontWeightBold,
        textAlign: 'center'
      },
      author: {
        display: 'flex',
        marginTop: theme.spacing(2)
      },
      authorInfo: {
        margin: theme.spacing(0, 2.5, 0, 1.5)
      },
      userName: {
        fontSize: 15,
        fontWeight: 'bold',
        color: theme.palette.text.primary,
        paddingRight: theme.spacing(0.5)
      },
      date: {
        fontSize: 13,
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(0.5)
      },
      itemContent: {
        fontSize: 15,
        lineHeight: 1.33,
        marginTop: theme.spacing(3),
        '& p + p': {
          marginBottom: theme.spacing(2.5)
        }
      },
      albumLabel: {
        color: '#aeacac'
      }
    }),
  { name: 'MuiSongViewDetail' }
);

export default useStyles;
