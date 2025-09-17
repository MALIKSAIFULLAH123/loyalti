/**
 * @type: service
 * name: BlockLoading
 */
import { LoadingComponentProps } from '@metafox/framework';
import { getErrString } from '@metafox/utils';
import { Alert, CircularProgress, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const BlockLoadingBackdrop = styled(Box, {
  name: 'BlockLoading',
  slot: 'Backdrop'
})({
  position: 'absolute',
  left: 0,
  top: 0,
  bottom: 0,
  right: 0
});

const BlockLoadingContent = styled(Box, {
  name: 'BlockLoading',
  slot: 'Content'
})({
  position: 'absolute',
  left: '50%',
  top: '50%',
  marginLeft: -20,
  marginTop: -20
});

export default function BlockLoadingComponent({
  error,
  size = 40,
  sx
}: LoadingComponentProps) {
  const msg = getErrString(error);

  return (
    <BlockLoadingBackdrop sx={sx}>
      <BlockLoadingContent>
        {error ? (
          <Alert variant="filled" color="error" children={msg} />
        ) : (
          <CircularProgress color="primary" size={size} />
        )}
      </BlockLoadingContent>
    </BlockLoadingBackdrop>
  );
}
