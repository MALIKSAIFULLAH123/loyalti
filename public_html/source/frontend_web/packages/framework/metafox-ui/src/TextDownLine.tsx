import { Box } from '@mui/material';
import React from 'react';

export default function TextDownLine({ text }: { text: string }) {
  if (!text) return;

  return <Box sx={{ whiteSpace: 'pre-line' }}>{text}</Box>;
}
