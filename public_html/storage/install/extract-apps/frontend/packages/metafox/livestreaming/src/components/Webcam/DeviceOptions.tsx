import React, { useEffect, useState } from 'react';
import { Select, MenuItem, FormControl, InputLabel, Box } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import { camelCase } from 'lodash';
import { styled, SxProps } from '@mui/material/styles';

const name = 'LivestreamingDeviceOptions';

const Wrapper = styled(Box, {
  name,
  slot: 'wrapper'
})(({ theme }) => ({
  display: 'flex',
  margin: theme.spacing(-1),
  '& > *': {
    flex: 1,
    minWidth: 0,
    padding: theme.spacing(1)
  }
}));

const Root = styled(Box, {
  name,
  slot: 'root'
})(({ theme }) => ({
  display: 'block'
}));

type ValuesProp = {
  video?: string;
  audio?: string;
};

const DeviceSelector = ({
  onDeviceChange,
  sx = {},
  defaultValues = {}
}: {
  onDeviceChange: (videoId: string, audioId: string) => void;
  sx?: SxProps;
  defaultValues?: ValuesProp;
}) => {
  const { i18n } = useGlobal();
  const [devices, setDevices] = useState<{
    video: MediaDeviceInfo[];
    audio: MediaDeviceInfo[];
  }>({
    video: [],
    audio: []
  });
  const [selectedVideo, setSelectedVideo] = useState<string>(
    defaultValues?.video || ''
  );
  const [selectedAudio, setSelectedAudio] = useState<string>(
    defaultValues?.audio || ''
  );

  useEffect(() => {
    async function getDevices() {
      const devices = await navigator.mediaDevices.enumerateDevices();

      setDevices({
        video: devices.filter(device => device.kind === 'videoinput'),
        audio: devices.filter(device => device.kind === 'audioinput')
      });
    }

    getDevices();
  }, []);

  return (
    <Root sx={sx}>
      <Wrapper>
        <Box>
          <FormControl fullWidth>
            <InputLabel>{i18n.formatMessage({ id: 'camera' })}</InputLabel>
            <Select
              data-testid={camelCase('select_camera')}
              value={selectedVideo}
              onChange={e => {
                setSelectedVideo(e.target.value);
                onDeviceChange(e.target.value, selectedAudio);
              }}
              label={i18n.formatMessage({ id: 'camera' })}
            >
              {devices.video.map(device => (
                <MenuItem key={device.deviceId} value={device.deviceId}>
                  {device.label ||
                    `${i18n.formatMessage({ id: 'camera' })} ${
                      devices.video.indexOf(device) + 1
                    }`}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
        <Box>
          <FormControl fullWidth>
            <InputLabel>{i18n.formatMessage({ id: 'microphone' })}</InputLabel>
            <Select
              data-testid={camelCase('select_audio')}
              value={selectedAudio}
              onChange={e => {
                setSelectedAudio(e.target.value);
                onDeviceChange(selectedVideo, e.target.value);
              }}
              label={i18n.formatMessage({ id: 'microphone' })}
            >
              {devices.audio.map(device => (
                <MenuItem key={device.deviceId} value={device.deviceId}>
                  {device.label ||
                    `${i18n.formatMessage({ id: 'microphone' })} ${
                      devices.video.indexOf(device) + 1
                    }`}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
      </Wrapper>
    </Root>
  );
};

export default DeviceSelector;
