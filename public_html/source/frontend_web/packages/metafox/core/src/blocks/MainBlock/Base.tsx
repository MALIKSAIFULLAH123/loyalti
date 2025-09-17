import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import React from 'react';

export interface Props extends BlockViewProps {
  initialValues: any;
}

export default function Base(props: Props) {
  const { useContentParams, jsxBackend } = useGlobal();
  const { mainBlock } = useContentParams();

  if (!mainBlock?.component) return null;

  return (
    <Block>
      <BlockContent>{jsxBackend.render(mainBlock)}</BlockContent>
    </Block>
  );
}
