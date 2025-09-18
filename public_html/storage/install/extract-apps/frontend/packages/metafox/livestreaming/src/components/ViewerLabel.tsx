/**
 * @type: ui
 * name: livestreaming.ui.labelViewer
 */
import React from 'react';
import { styled, Typography } from '@mui/material';
import { LineIcon, FormatNumber } from '@metafox/ui';
import {
  useFirestoreDocIdListener,
  useFirebaseFireStore
} from '@metafox/framework/firebase';

const name = 'FlagLiveLabel';

const FlagLiveLabel = styled(Typography, {
  name,
  slot: 'packageOuter',
  shouldForwardProp: props => props !== 'backgroundColor'
})<{ backgroundColor?: string }>(({ theme, backgroundColor }) => ({
  height: '24px',
  display: 'inline-flex',
  padding: `0 ${theme.spacing(1)}`,
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: 'rgba(0,0,0,0.4)',
  color: theme.palette.common.white,
  fontSize: theme.typography.body2.fontSize,
  textTransform: 'uppercase',
  borderRadius: '4px',
  '& > *': {
    margin: '0 4px'
  }
}));

export default function ViewerLabel({ streamKey, ...rest }) {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const db = useFirebaseFireStore();
  const viewerData = useFirestoreDocIdListener(db, {
    collection: 'live_video_view',
    docID: streamKey
  });
  const total = viewerData?.total_viewer ?? viewerData?.view?.length;

  if (!streamKey || !total) return null;

  return (
    <FlagLiveLabel {...rest}>
      <LineIcon icon={'ico-eye-alt'} />
      <FormatNumber value={total} />
    </FlagLiveLabel>
  );
}
