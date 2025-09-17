import { CircularProgress } from '@mui/material';
import React from 'react';

const styles: {
  backdrop: React.CSSProperties;
  content: React.CSSProperties;
} = {
  backdrop: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0
  },
  content: {
    position: 'absolute',
    left: '50%',
    top: '50%',
    marginLeft: -20,
    marginTop: -20
  }
};

export default function Loading({ size = 40 }: { size?: number }) {
  return (
    <div style={styles.backdrop} data-testid="loadingIndicator">
      <div style={styles.content}>
        <CircularProgress variant="indeterminate" size={size} />
      </div>
    </div>
  );
}
