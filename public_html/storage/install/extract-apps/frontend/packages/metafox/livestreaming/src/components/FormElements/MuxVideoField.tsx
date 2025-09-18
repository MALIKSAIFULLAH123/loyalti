/**
 * @type: formElement
 * name: form.element.MuxPlayer
 * chunkName: formElement.livestreaming
 */

import { FormFieldProps } from '@metafox/form/types';
import { FormControl, Box, styled, Typography } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { useField } from 'formik';
import loadable from '@loadable/component';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';

const MuxPlayer = loadable(
  () =>
    import(
      /* webpackChunkName: "VideoPlayer" */
      '@mux/mux-player-react'
    )
);

const name = 'FieldMuxPlayer';

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
            id: 'connect_streaming_software_to_go_live'
          })}
        </Box>
      </Typography>
    </PlaceholderRoot>
  );
};

export default function MuxPlayerField({
  config: {
    excludeFields,
    label = 'Reset',
    align = 'right',
    size,
    margin,
    fullWidth,
    sxFieldWrapper,
    streamKey
  },
  name,
  formik
}: FormFieldProps) {
  const [, , { setValue }] = useField(name ?? 'MuxVideoField');
  const [data, setData] = React.useState<Record<string, any>>();
  const videoRef = React.useRef();
  const db = useFirebaseFireStore();
  const dataLive = useFirestoreDocIdListener(db, {
    collection: 'live_video',
    docID: streamKey
  });

  React.useEffect(() => {
    setData(dataLive);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dataLive]);

  React.useEffect(() => {
    setValue(data?.status);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  const isVideoStreaming = ['waiting', 'active'].includes(data?.status);

  return (
    <FormControl
      size={size}
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      {isVideoStreaming ? (
        <MuxPlayer
          playbackId={data?.playback?.playback_id}
          streamType="on-demand"
          ref={videoRef}
          style={{
            '--seek-backward-button': 'none',
            '--seek-forward-button': 'none',
            '--time-display': 'none',
            '--playback-rate-button': 'none',
            '--pip-button': 'none'
          }}
        />
      ) : (
        <PlaceHolder />
      )}
    </FormControl>
  );
}
