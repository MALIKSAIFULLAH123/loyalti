import HtmlViewer from '@metafox/html-viewer';
import { CommentContentProps } from '@metafox/comment/types';
import { TruncateViewMore } from '@metafox/ui';
import { styled, Typography, Box } from '@mui/material';
import React from 'react';
import CommentExtraData from './ExtraData';
import { useGlobal } from '@metafox/framework';

const BubbleText = styled('div', {
  name: 'CommentContent',
  slot: 'bubbleText'
})(({ theme }) => ({
  display: 'block',
  fontSize: theme.mixins.pxToRem(15),
  transition: 'background 300ms ease',
  '.ignore-tag': {
    color: theme.palette.text.secondary
  },
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
  isHidden,
  identity
}: CommentContentProps) {
  const { i18n, jsxBackend, getAcl } = useGlobal();
  const enableTranslate = getAcl('comment.comment.translate');

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
            {enableTranslate
              ? jsxBackend.render({
                  component: 'core.ui.detectLanguageButton',
                  props: {
                    text,
                    identity,
                    TranslateView: ({ result }) => (
                      <Box mt={2}>
                        <HtmlViewer html={result?.translated_text} />
                      </Box>
                    )
                  }
                })
              : null}
          </BubbleText>
          <CommentExtraData extra_data={extra_data} />
        </>
      )}
    </div>
  );
}
