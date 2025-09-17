import { RefOf } from '@metafox/framework';
import { EmojiSetShape, OnEmojiClick } from '@metafox/emoji/types';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const EmojiListRoot = styled('ul', {
  name: 'EmojiPicker',
  slot: 'list'
})(({ theme }) => ({
  listStyle: 'none none outside',
  margin: 4,
  padding: 0,
  display: 'flex',
  flexWrap: 'wrap',
  justifyContent: 'flex-start',
  width: 282
}));

const EmojiListItem = styled('li', {
  name: 'EmojiPicker',
  slot: 'item'
})(({ theme }) => ({
  display: 'inline-flex',
  width: 24,
  height: 24,
  margin: 2,
  fontSize: theme.typography.h4.fontSize,
  overflow: 'hidden'
}));

export interface Props {
  data: EmojiSetShape;
  onEmojiClick?: OnEmojiClick;
}

function EmojiList({ data, onEmojiClick }: Props, ref: RefOf<HTMLDivElement>) {
  return (
    <Box ref={ref}>
      <Typography variant="subtitle2" component="h4" sx={{ p: [1, 1, 0, 1] }}>
        {data.label}
      </Typography>
      <EmojiListRoot>
        {data.emojis.map((x, index) => (
          <EmojiListItem
            role="button"
            aria-label="emoji"
            data-testid="emoji"
            key={index.toString()}
            onClick={() => onEmojiClick(x)}
          >
            {x}
          </EmojiListItem>
        ))}
      </EmojiListRoot>
    </Box>
  );
}

export default React.forwardRef<HTMLDivElement, Props>(EmojiList);
