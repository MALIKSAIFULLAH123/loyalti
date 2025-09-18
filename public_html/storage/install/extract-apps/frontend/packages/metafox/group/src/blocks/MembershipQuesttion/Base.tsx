import { BlockViewProps } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Box } from '@mui/material';
import * as React from 'react';
import MembershipQuestionList from './MembershipQuestionList/Block';
import MembershipQuestionMode from './MembershipQuestionMode/Block';

export type Props = BlockViewProps & { identity: string };

export default function GroupRule({ identity, ...rest }: Props) {
  if (!identity) return null;

  return (
    <Block {...rest}>
      <BlockContent>
        <Box>
          <MembershipQuestionMode identity={identity} />
          <MembershipQuestionList />
        </Box>
      </BlockContent>
    </Block>
  );
}
