import { BlockViewProps } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box } from '@mui/material';
import * as React from 'react';
import RuleMode from './RuleMode/Block';
import RulePost from './RulePost/Block';

export type Props = BlockViewProps & { identity: string };

export default function GroupRule({ identity, ...rest }: Props) {
  if (!identity) return null;

  return (
    <Block {...rest}>
      <BlockContent>
        <Box>
          <RuleMode identity={identity} />
          <RulePost />
        </Box>
      </BlockContent>
    </Block>
  );
}
