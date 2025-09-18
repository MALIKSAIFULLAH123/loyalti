import { BlockViewProps, useResourceAction } from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
import { Box, CircularProgress, styled } from '@mui/material';
import * as React from 'react';
import { APP_GROUP, RESOURCE_INTEGRATED } from '@metafox/group';
import { RemoteFormBuilder } from '@metafox/form';

const name = 'GroupMenu';

const BlockContentCustom = styled(BlockContent, { name, slot: 'blockContent' })(
  () => ({
    '&:empty': {
      display: 'none'
    }
  })
);

export default function GroupMenu({ title }: BlockViewProps) {
  const { id } = usePageParams();
  const dataSource = useResourceAction(
    APP_GROUP,
    RESOURCE_INTEGRATED,
    'menuSetting'
  );

  if (!dataSource) return null;

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContentCustom>
        <RemoteFormBuilder
          noHeader
          dataSource={dataSource}
          pageParams={{ id }}
          keepPaginationData
          loadingComponent={
            <Box sx={{ display: 'flex', justifyContent: 'center' }}>
              <CircularProgress size={24} />
            </Box>
          }
          hideWhenError
        />
      </BlockContentCustom>
    </Block>
  );
}
