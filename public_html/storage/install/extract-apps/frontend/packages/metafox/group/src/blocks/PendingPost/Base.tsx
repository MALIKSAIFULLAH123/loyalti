import { BlockViewProps } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box } from '@mui/material';
import * as React from 'react';
import PendingMode from './PendingMode/Block';
import PendingPost from './PendingPost/Block';

export type Props = BlockViewProps & { identity: string };

export default function PendingPosts({ identity, title, ...rest }: Props) {
  if (!identity) return null;

  return (
    <Block {...rest}>
      <BlockContent>
        <Box>
          <PendingMode identity={identity} />
          <PendingPost />
        </Box>
      </BlockContent>
    </Block>
  );
}
