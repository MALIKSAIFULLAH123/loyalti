/**
 * @type: ui
 * name: livestreaming.ui.composerReplyInfo
 */

import { useGlobal } from '@metafox/framework';
import { TruncateText, LineIcon } from '@metafox/ui';
import { Box, IconButton } from '@mui/material';
import React from 'react';
import { isEmpty } from 'lodash';

export default function Reply({ item, removeReply, sx }) {
  const { i18n } = useGlobal();

  if (isEmpty(item)) return null;

  return (
    <Box pt={1} sx={{ borderTop: '1px solid rgba(0,0,0,0.1)', ...sx }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
        <Box mb={1} color={'text.hint'}>
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
          </TruncateText>
        </Box>
        <IconButton
          onClick={removeReply}
          sx={{ fontSize: '12px', width: '24px', height: '24px' }}
        >
          <LineIcon icon="ico-close" />
        </IconButton>
      </Box>
    </Box>
  );
}
