import { useGlobal } from '@metafox/framework';
import { styled, Typography } from '@mui/material';
import React from 'react';

const DraftText = styled('span', {
  name: 'DraftFlag'
})(({ theme }) => ({
  color: theme.palette.warning.main,
  marginRight: theme.spacing(0.5)
}));

export default function DraftFlag({
  value,
  ...props
}: {
  value: Boolean;
  [x: string]: any;
}) {
  const { i18n } = useGlobal();

  if (!value) return null;

  return (
    <DraftText>
      <Typography {...props}>
        {`[${i18n.formatMessage({
          id: 'draft'
        })}]`}
      </Typography>
    </DraftText>
  );
}
