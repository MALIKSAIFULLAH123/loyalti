import HtmlViewer from '@metafox/html-viewer';
import { TruncateViewMore } from '@metafox/ui';
import { styled, Typography } from '@mui/material';
import React from 'react';
import CommentExtraData from './ExtraData';
import { useGlobal } from '@metafox/framework';
import { CommentContentProps } from '@metafox/livestreaming/types';

const BubbleText = styled('div', {
  name: 'CommentContent',
  slot: 'bubbleText'
})(({ theme }) => ({
  display: 'block',
  fontSize: theme.mixins.pxToRem(15),
  transition: 'background 300ms ease',
  '& a': {
    color: theme.palette.primary.main,
    fontWeight: theme.typography.fontWeightRegular,
    '&:hover': {
      textDecoration: 'underline'
    }
  }
}));

export default function CommentContent({
  text,
  extra_data,
  isHidden
}: CommentContentProps) {
  const { i18n } = useGlobal();

  return (
    <div>
      {isHidden ? (
        <BubbleText>
          <Typography component="h1" variant="body1" color="text.hint">
            {i18n.formatMessage({
              id: 'comment_hidden'
            })}
          </Typography>
        </BubbleText>
      ) : (
        <>
          <BubbleText>
            <TruncateViewMore
              truncateProps={{
                variant: 'body1',
                lines: 3
              }}
            >
              <HtmlViewer html={text} />
            </TruncateViewMore>
          </BubbleText>
          <CommentExtraData extra_data={extra_data} />
        </>
      )}
    </div>
  );
}
