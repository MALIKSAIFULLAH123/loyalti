import ExternalAPI from './external_api';

export default function injectCallFrame(
  callInfo: any,
  user: any,
  readyToClose: any
) {
  const {
    callId,
    group,
    audioOnly: startVideoMuted,
    canEnableVideo = true,
    localDisplayName,
    localAvatar,
    url,
    jwt,
    subjectTitle
  } = callInfo;

  const toolbarButtons = [
    'recording',
    'microphone',
    'hangup',
    'fullscreen',
    'videoquality',
    'filmstrip',
    'tileview',
    'download',
    'desktop',
    // 'mute-everyone'
    // 'livestreaming',
    'sharedvideo',
    ...(canEnableVideo ? ['camera'] : [])
  ].filter(Boolean);

  const urlObj = new URL(url);

  // @link https://github.com/jitsi/jitsi-meet/blob/master/interface_config.js
  const api = new ExternalAPI(urlObj.host, {
    roomName: urlObj.pathname,
    noSSL: !/https/i.test(urlObj.protocol),
    parentNode: document.querySelector('#meet'),
    jwt,
    configOverwrite: {
      startAudioOnly: !canEnableVideo,
      startVideoMuted,
      startWithVideoMuted: startVideoMuted,
      prejoinPageEnabled: false,
      enableClosePage: false,
      enableWelcomePage: false,
      hideConferenceSubject: true
    },
    interfaceConfigOverwrite: {
      SHOW_PROMOTIONAL_CLOSE_PAGE: false,
      SHOW_WATERMARK_FOR_GUESTS: false,
      SHOW_BRAND_WATERMARK: false,
      BRAND_WATERMARK_LINK: '',
      SHOW_POWERED_BY: false,
      DEFAULT_REMOTE_DISPLAY_NAME: localDisplayName,
      DEFAULT_LOCAL_DISPLAY_NAME: localDisplayName,
      SHOW_JITSI_WATERMARK: false,
      // JITSI_WATERMARK_LINK: 'https://jitsi.org',
      DEFAULT_BACKGROUND: '#1B2638',
      DISABLE_VIDEO_BACKGROUND: true,
      DISABLE_RINGING: false,
      VIDEO_QUALITY_LABEL_DISABLED: true,
      RECENT_LIST_ENABLED: false,
      AUTO_PIN_LATEST_SCREEN_SHARE: false,
      DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
      TOOLBAR_BUTTONS: toolbarButtons,
      SETTINGS_SECTIONS: []
    }
  });

  setTimeout(() => {
    api.executeCommand('avatarUrl', localAvatar);
    api.executeCommand('displayName', localDisplayName);
  }, 2000);

  api.addListener('videoConferenceJoined', () => {
    api.executeCommand('avatarUrl', localAvatar);
  });
  // Keep room subject consistent
  api.executeCommand('subject', subjectTitle);

  if (!group) {
    api.addEventListener('videoConferenceLeft', readyToClose);
  }

  window.addEventListener('message', evt => {
    if (!evt || !evt.data) {
      return;
    }

    if (evt.data === `hangup/${callId}`) {
      api.executeCommand('hangup');
    }
  });

  api.addEventListener('readyToClose', readyToClose);
  api.addEventListener('feedbackSubmitted', () => {
    setTimeout(readyToClose, 3000);
  });
}
