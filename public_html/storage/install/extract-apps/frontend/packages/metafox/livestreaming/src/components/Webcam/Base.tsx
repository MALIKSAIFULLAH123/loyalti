import { useGlobal } from '@metafox/framework';
import { Box, styled, SxProps, Typography } from '@mui/material';
import * as React from 'react';
import DeviceSelector from './DeviceOptions';
import { LivestreamConfig } from '@metafox/livestreaming';
import { LineIcon } from '@metafox/ui';
import { isEqual } from 'lodash';

type ValuesProp = {
  video?: string;
  audio?: string;
};

const name = 'LivestreamWebcam';

const Root = styled(Box, {
  name,
  slot: 'root'
})(({ theme }) => ({
  position: 'relative',
  display: 'block',
  '& video': {
    width: '100%'
  }
}));

const PlaceholderRoot = styled(Box, {
  name,
  slot: 'placeholder'
})(({ theme }) => ({
  position: 'relative',
  backgroundColor: '#333',
  color: '#fff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  '& video': {
    position: 'absolute',
    top: 0,
    left: 0,
    width: '100%',
    height: '100%'
  },
  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '56.25%'
  }
}));

const PlaceHolder = () => {
  const { i18n } = useGlobal();

  return (
    <PlaceholderRoot>
      <Typography
        variant="body1"
        sx={{
          display: 'flex',
          alignItems: 'center',
          flexDirection: 'column',
          justifyContent: 'center',
          fontSize: '24px'
        }}
      >
        <LineIcon icon="ico-videocam" sx={{ fontSize: 40, mb: 2 }} />
        <Box ml={1}>
          {i18n.formatMessage({
            id: 'connect_devices_to_go_live'
          })}
        </Box>
      </Typography>
    </PlaceholderRoot>
  );
};
type DeviceConfig = {
  video?: string;
  audio?: string;
};
type Props = {
  onReady?: (data: any) => void;
  streamKey?: string;
  sxDeviceWrapper?: SxProps;
  deviceDefault?: DeviceConfig;
  id?: number;
};
const WebcamLiveStream = ({
  onReady,
  streamKey,
  sxDeviceWrapper,
  deviceDefault: deviceDefaultProp,
  id
}: Props) => {
  const {
    dispatch,
    getSetting,
    livestreamingSocket,
    dialogBackend,
    i18n,
    setNavigationConfirm
  } = useGlobal();
  const videoRef = React.useRef<HTMLVideoElement>(null);
  const recorderRef = React.useRef<MediaRecorder | null>(null);
  const streamKeyRef = React.useRef<string | undefined>(undefined);
  const { per_time_recorder_webcam = 1000 } =
    getSetting<LivestreamConfig>('livestreaming');
  const refStream = React.useRef<MediaStream | null>(null);
  const status = livestreamingSocket.getStatus();
  const [initSocket, setInitSocket] = React.useState(status === 'initialized');
  const [initDevice, setInitDevice] = React.useState(false);
  const [deviceState, setDeviceState] =
    React.useState<ValuesProp>(deviceDefaultProp);

  // Get optimal settings based on network
  const getOptimalSettings = React.useCallback(() => {
    const connection = (navigator as any).connection;
    const effectiveType = connection?.effectiveType || '4g';

    const bitrateSettings = {
      '4g': {
        video: 2500000, // 2.5 Mbps
        audio: 128000, // 128 Kbps
        framerate: 30
      },
      '3g': {
        video: 1000000, // 1 Mbps
        audio: 64000, // 64 Kbps
        framerate: 24
      },
      '2g': {
        video: 500000, // 500 Kbps
        audio: 32000, // 32 Kbps
        framerate: 15
      }
    };

    return bitrateSettings[effectiveType] || bitrateSettings['4g'];
  }, []);

  // Get optimal video constraints
  const getVideoConstraints = React.useCallback((settings: any) => {
    const performance = window.performance as any;
    const isHighMemory = performance.memory?.usedJSHeapSize > 500000000;

    return {
      width: { ideal: isHighMemory ? 854 : 1280 },
      height: { ideal: isHighMemory ? 480 : 720 },
      frameRate: { ideal: settings.framerate }
    };
  }, []);

  // Get supported MIME type
  const getSupportedMimeType = React.useCallback(() => {
    // First try codecs with audio support
    const audioCodecs = [
      'video/webm; codecs=vp9,opus',
      'video/webm; codecs=vp8,opus',
      'video/mp4; codecs=h264,aac'
    ];

    for (const type of audioCodecs) {
      if (MediaRecorder.isTypeSupported(type)) {
        return type;
      }
    }

    // Fallback to video-only codecs
    const videoCodecs = [
      'video/webm; codecs=vp9',
      'video/webm; codecs=vp8',
      'video/webm',
      'video/mp4'
    ];

    for (const type of videoCodecs) {
      if (MediaRecorder.isTypeSupported(type)) {
        return type;
      }
    }

    return 'video/webm';
  }, []);

  // Initialize streaming connection
  const initStreamingConnection = React.useCallback(async () => {
    if (!streamKey || !livestreamingSocket) return true;

    const result = await livestreamingSocket.waitDdpMethod({
      name: 'streaming',
      id: 'result/streaming',
      params: [streamKey]
    });

    if (result.error) {
      dialogBackend.alert({
        message: result.msg ? i18n.formatMessage({ id: result.msg }) : undefined
      });

      return false;
    }

    return true;
  }, [streamKey, livestreamingSocket, dialogBackend, i18n]);

  // Create MediaRecorder with optimal settings
  const createMediaRecorder = React.useCallback(
    (stream: MediaStream) => {
      const settings = getOptimalSettings();
      const mimeType = getSupportedMimeType();

      // Check if audio track exists
      const hasAudio = stream.getAudioTracks().length > 0;
      const supportsAudio =
        mimeType.includes('opus') || mimeType.includes('aac');

      const recorder = new MediaRecorder(stream, {
        mimeType,
        videoBitsPerSecond: settings.video,
        audioBitsPerSecond: hasAudio && supportsAudio ? settings.audio : 0
      });

      if (streamKeyRef.current && livestreamingSocket) {
        recorder.ondataavailable = async event => {
          if (event.data.size > 0 && livestreamingSocket) {
            livestreamingSocket.sendRaw(event.data);
          }
        };
      }

      return recorder;
    },
    [getOptimalSettings, getSupportedMimeType, livestreamingSocket]
  );

  // Handle network changes
  const handleNetworkChange = React.useCallback(() => {
    if (!refStream.current || !recorderRef.current) return;

    recorderRef.current.stop();
    const newRecorder = createMediaRecorder(refStream.current);
    recorderRef.current = newRecorder;
    newRecorder.start(per_time_recorder_webcam);
  }, [createMediaRecorder, per_time_recorder_webcam]);

  // Setup network change listener
  React.useEffect(() => {
    const connection = (navigator as any).connection;

    if (connection) {
      connection.addEventListener('change', handleNetworkChange);

      return () =>
        connection.removeEventListener('change', handleNetworkChange);
    }
  }, [handleNetworkChange]);

  const startStream = async () => {
    if (refStream.current) {
      refStream.current.getTracks().forEach(track => track.stop());
    }

    const videoId = deviceState?.video;
    const audioId = deviceState?.audio;

    try {
      // Initialize streaming connection first
      if (streamKey) {
        streamKeyRef.current = streamKey;
        const success = await initStreamingConnection();

        if (!success) return;
      }

      const settings = getOptimalSettings();
      const videoConstraints = getVideoConstraints(settings);

      const newStream = await navigator.mediaDevices.getUserMedia({
        video: {
          deviceId: videoId ? { exact: videoId } : undefined,
          ...videoConstraints
        },
        audio: {
          deviceId: audioId ? { exact: audioId } : undefined,
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: true
        }
      });

      setInitDevice(true);
      const videoTrack = newStream.getVideoTracks()[0];
      const audioTrack = newStream.getAudioTracks()[0];
      const deviceValues = {
        video: videoTrack?.getSettings().deviceId,
        audio: audioTrack?.getSettings().deviceId
      };

      if (!isEqual(deviceValues, deviceState)) {
        setDeviceState(deviceValues);
      }

      if (videoRef.current) {
        videoRef.current.srcObject = newStream;
      }

      refStream.current = newStream;
      onReady && onReady(deviceValues);

      const recorder = createMediaRecorder(newStream);
      recorderRef.current = recorder;
      recorder.start(per_time_recorder_webcam);
    } catch (error) {
      console.error('Error starting stream webcam:', error);

      // Fallback to lower quality settings but keep audio
      try {
        const fallbackStream = await navigator.mediaDevices.getUserMedia({
          video: {
            width: { ideal: 640 },
            height: { ideal: 480 },
            frameRate: { ideal: 15 }
          },
          audio: {
            deviceId: audioId ? { exact: audioId } : undefined,
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true
          }
        });

        if (videoRef.current) {
          videoRef.current.srcObject = fallbackStream;
        }

        refStream.current = fallbackStream;
      } catch (fallbackError) {
        console.error('Fallback stream failed:', fallbackError);
      }
    }
  };

  const onDeviceChange = (videoId?: string, audioId?: string) => {
    setDeviceState({ video: videoId, audio: audioId });
  };

  React.useEffect(() => {
    dispatch({
      type: 'livestreaming/socket/init',
      meta: {
        onSuccess: () => {
          setInitSocket(true);
        }
      }
    });

    if (streamKey) {
      setNavigationConfirm(
        true,
        {
          message: i18n.formatMessage({
            id: 'live_video_is_on_going_are_you_sure_to_leave_now'
          })
        },
        () => {
          dispatch({ type: 'livestreaming/end-live/force', payload: { id } });
        }
      );
      window.onbeforeunload = () => {
        return i18n.formatMessage({
          id: 'live_video_is_on_going_are_you_sure_to_leave_now'
        });
      };
    }

    return () => {
      if (refStream.current) {
        refStream.current.getTracks().forEach(track => track.stop());
      }

      if (streamKey) {
        livestreamingSocket.close();
        window.onbeforeunload = undefined;
        setNavigationConfirm(false);
      }
    };
  }, []);

  React.useEffect(() => {
    if (initSocket) {
      startStream();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initSocket, deviceState?.video, deviceState?.audio]);

  return (
    <Root>
      <Box>
        {!initDevice ? <PlaceHolder /> : null}
        <PlaceholderRoot sx={{ display: initDevice ? 'block' : 'none' }}>
          <video
            ref={videoRef}
            autoPlay
            playsInline
            controls={false}
            muted
            // keep video ele rendered to insert stream
          />
        </PlaceholderRoot>
        {deviceState ? (
          <DeviceSelector
            defaultValues={deviceState}
            sx={sxDeviceWrapper}
            onDeviceChange={onDeviceChange}
          />
        ) : null}
      </Box>
    </Root>
  );
};

export default WebcamLiveStream;
