import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { UIBlockViewProps } from '@metafox/ui';
import { Box, Divider } from '@mui/material';
import React from 'react';
import RemoteApiButton from './RemoteApiButton';

export interface Props extends UIBlockViewProps {}

export default function AdminMaintainCache({ title, blockProps }: Props) {
  const { i18n } = useGlobal();

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent style={{ height: 400, padding: 16 }}>
        <Box paddingY={2}>
          <RemoteApiButton
            color="primary"
            variant="contained"
            size="large"
            apiMethod="delete"
            apiUrl="/admincp/cache/config"
            bootstrap
          >
            {i18n.formatMessage({ id: 'Flush Config Cache' })}
          </RemoteApiButton>
        </Box>
        <Divider />
        <Box paddingY={2}>
          <RemoteApiButton
            color="primary"
            variant="contained"
            size="large"
            apiMethod="delete"
            apiUrl="/admincp/cache/data"
          >
            {i18n.formatMessage({ id: 'Flush Data Cache' })}
          </RemoteApiButton>
        </Box>
      </BlockContent>
    </Block>
  );
}
