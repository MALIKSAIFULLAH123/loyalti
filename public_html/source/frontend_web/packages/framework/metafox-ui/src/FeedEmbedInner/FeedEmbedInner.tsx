import { TruncateText } from '@metafox/ui';
import { Box, Theme, TypographyVariant } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {}
    }),
  { name: 'MuiFeedEmbedInner' }
);

interface Props {
  content: string;
  variant?: TypographyVariant;
  lines?: 1 | 2 | 3;
}

export default function FeedEmbedInner({
  content,
  variant = 'body1',
  lines = 3
}: Props) {
  const classes = useStyles();

  return (
    <Box className={classes.root}>
      <TruncateText variant={variant} lines={lines}>
        <div dangerouslySetInnerHTML={{ __html: content }} />
      </TruncateText>
    </Box>
  );
}
