import {
  BlockViewProps,
  useGlobal,
  RemoteDataSource
} from '@metafox/framework';
import { RemoteFormBuilder } from '@metafox/form';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { ForumThreadShape } from '@metafox/forum/types';
import { styled, CircularProgress, Box } from '@mui/material';

export type Props = BlockViewProps & {
  dataSource?: RemoteDataSource;
};
const name = 'ForumPostForm';

const BlockContentCustom = styled(BlockContent, { name, slot: 'blockContent' })(
  () => ({
    '&:empty': {
      display: 'none'
    }
  })
);

export default function PostAdd({ dataSource }: Props) {
  const { usePageParams, useLoggedIn, useGetItem } = useGlobal();
  const loggedIn = useLoggedIn();
  const { id, identity } = usePageParams();
  const thread: ForumThreadShape = useGetItem(identity);

  if (!thread) return null;

  const { is_closed, extra } = thread;

  if (!dataSource || !loggedIn || is_closed || !extra?.can_reply) return null;

  return (
    <Block>
      <BlockContentCustom>
        <RemoteFormBuilder
          noHeader
          dataSource={dataSource}
          pageParams={{ id }}
          resetFormWhenSuccess
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
