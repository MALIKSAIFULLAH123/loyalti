import { Chip, ChipProps } from '@mui/material';
import React from 'react';

export default function ChipControl({ label, onDelete }: ChipProps) {
  return <Chip label={label} onDelete={onDelete} />;
}
