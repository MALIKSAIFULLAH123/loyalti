import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { TruncateText, LineIcon } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'Reply-LiveVideo';

const RootReply = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  marginBottom: theme.spacing(1),
  color: theme.palette.text.hint,
  a: {
    color: theme.palette.text.hint,
    pointerEvents: 'none'
  }
}));

export default function Reply({ item }: { item: Record<string, any> }) {
  const { i18n } = useGlobal();

  return (
    <RootReply>
      <TruncateText variant={'body2'} lines={2}>
        <Box
          mr={1}
          component={'span'}
          sx={{ display: 'inline-flex', transform: 'scaleX(-1)' }}
        >
          <LineIcon icon="ico-reply" />
        </Box>
        {i18n.formatMessage(
          {
            id: 'replying_to_user'
          },
          {
            user_name: item?.user_full_name
          }
        )}
        {item.text ? (
          <>
            : <HtmlViewer html={item.text} />
          </>
        ) : null}
      </TruncateText>
    </RootReply>
  );
}
