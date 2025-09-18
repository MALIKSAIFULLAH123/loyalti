import loadable from '@loadable/component';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const UIChatAudioCustom = styled('div', {
  slot: 'uiChatAudioCustom',
  shouldForwardProp: props =>
    props !== 'isPageAllMessages' &&
    props !== 'msgType' &&
    props !== 'isOwner' &&
    props !== 'isOther'
})<{
  isPageAllMessages?: boolean;
  msgType?: string;
  isOwner?: boolean;
  isOther?: boolean;
}>(({ theme, isPageAllMessages, msgType, isOwner, isOther }) => ({
  width: '300px',
  maxWidth: '100%',
  '& .rhap_container': {
    borderRadius: '4px',
    boxSizing: 'border-box',
    display: 'flex',
    flexDirection: 'column',
    lineHeight: 1,
    fontFamily: 'inherit',
    width: '100%',
    padding: '10px 15px',
    backgroundColor: '#fff',
    boxShadow: '0 0 3px 0 rgba(0, 0, 0, 0.2)'
  },

  '& .rhap_main': {
    display: 'flex',
    flexDirection: 'column',
    flex: '1 1 auto',
    '& .rhap_button-clear': {
      backgroundColor: 'transparent',
      border: 'none',
      padding: 0,
      overflow: 'hidden',
      cursor: 'pointer'
    }
  },

  '& .rhap_progress-section': {
    display: 'flex',
    flex: '3 1 auto',
    alignItems: 'center',
    // html[dir='rtl'] & {
    // flexDirection: 'row-reverse'
    // },
    '& .rhap_time': {
      color: '#555555',
      userSelect: 'none',
      fontSize: isPageAllMessages ? '16px' : '12px'
    },
    '& .rhap_progress-container': {
      display: 'flex',
      alignItems: 'center',
      height: '20px',
      flex: '1 0 auto',
      alignSelf: 'center',
      margin: '0 calc(11px)',
      cursor: 'pointer',
      userSelect: 'none',
      '& .rhap_progress-bar-show-download': {
        backgroundColor: 'rgba(221, 221, 221, 0.5)'
      },
      '& .rhap_progress-bar': {
        boxSizing: 'border-box',
        position: 'relative',
        zIndex: 0,
        width: '100%',
        height: '5px',
        backgroundColor: '#dddddd',
        borderRadius: '2px',
        '& .rhap_progress-indicator': {
          boxSizing: 'border-box',
          position: 'absolute',
          zIndex: 3,
          width: '20px',
          height: '20px',
          marginLeft: '-10px',
          top: '-8px',
          background: '#868686',
          borderRadius: '50px',
          boxShadow: 'rgb(134 134 134 / 50%) 0 0 5px'
        },
        '& .rhap_progress-filled': {
          height: '100%',
          position: 'absolute',
          zIndex: 2,
          backgroundColor: '#868686',
          borderRadius: '2px',
          left: 0
        },

        '& .rhap_download-progress': {
          width: '100%',
          height: '100%',
          position: 'absolute',
          zIndex: 1,
          backgroundColor: '#dddddd',
          borderRadius: '2px'
        }
      }
    }
  },
  '& .rhap_controls-section': {
    marginTop: '8px',
    flex: '1 1 auto',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    '& .rhap_additional-controls': {
      display: 'none',
      flex: '1 1 auto',
      alignItems: 'center'
    },
    '& .rhap_main-controls': {
      paddingRight: '8px',
      flex: 'none',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      '& .rhap_play-pause-button': {
        fontSize: '40px',
        width: '40px',
        height: '40px'
      },
      '& .rhap_main-controls-button': {
        margin: '0 3px',
        color: '#868686',
        fontSize: '35px',
        width: '35px',
        height: '35px',
        display: 'inline-flex',
        alignItems: 'center'
      }
    },
    '& .rhap_volume-controls': {
      flex: 1,
      minWidth: 0,
      flexBasis: 'auto',
      paddingRight: '8px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'flex-end',
      '& .rhap_volume-container': {
        display: 'flex',
        alignItems: 'center',
        flex: '0 1 100px',
        userSelect: 'none',
        '& .rhap_volume-button': {
          flex: '0 0 26px',
          fontSize: '26px',
          width: '26px',
          height: '26px',
          color: '#868686',
          marginRight: '6px'
        },
        '& .rhap_volume-bar-area': {
          display: 'flex',
          alignItems: 'center',
          width: '100%',
          height: '14px',
          cursor: 'pointer',
          '& .rhap_volume-bar': {
            boxSizing: 'border-box',
            position: 'relative',
            width: '100%',
            height: '4px',
            background: '#dddddd',
            borderRadius: '2px',
            '& .rhap_volume-indicator': {
              boxSizing: 'border-box',
              position: 'absolute',
              width: '12px',
              height: '12px',
              marginLeft: '-6px',
              left: 0,
              top: '-4px',
              background: '#868686',
              opacity: 0.9,
              borderRadius: '50px',
              boxShadow: 'rgb(134 134 134 / 50%) 0 0 3px',
              cursor: 'pointer'
            }
          }
        }
      }
    }
  }
}));

const AudioPlayer = loadable(
  () => import(/* webpackChunkName: "AudioPlayer" */ 'react-h5-audio-player')
);

interface Props {
  msgType?: string;
  isOwner?: boolean;
  audio_url: string;
  isOther?: boolean;
}
export default function MsgAttachmentAudio({
  audio_url,
  msgType,
  isOwner,
  isOther
}: Props) {
  const { usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  return (
    <UIChatAudioCustom
      isPageAllMessages={isPageAllMessages}
      msgType={msgType}
      isOwner={isOwner}
      isOther={isOther}
    >
      <AudioPlayer
        src={audio_url}
        customAdditionalControls={[]}
        showJumpControls={false}
      />
    </UIChatAudioCustom>
  );
}
