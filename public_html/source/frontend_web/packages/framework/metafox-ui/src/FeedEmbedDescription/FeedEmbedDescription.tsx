import { TruncateText, TruncateTextProps } from '@metafox/ui';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {}
    }),
  { name: 'MuiFeedEmbedDescription' }
);

interface Props extends TruncateTextProps {
  content: string;
  lines?: 1 | 2 | 3;
}

export default function FeedEmbedDescription({
  content,
  variant = 'body1',
  color = 'textSecondary',
  lines = 3
}: Props) {
  const classes = useStyles();

  return (
    <TruncateText
      variant={variant}
      lines={lines}
      color={color}
      className={classes.root}
    >
      <div dangerouslySetInnerHTML={{ __html: content }} />
    </TruncateText>
  );
}
