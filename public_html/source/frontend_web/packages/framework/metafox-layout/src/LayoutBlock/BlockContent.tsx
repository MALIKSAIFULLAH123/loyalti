/**
 * @type: ui
 * name: ui.block.default.content
 */
import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import BlockDivider from './BlockDivider';

interface Props {
  children?: React.ReactNode;
  style?: React.CSSProperties;
}

export type BlockWrapperProps = {
  children?: any;
  testid?: string;
  blockProps: any;
};

const BlockContent = styled(Box, {
  name: 'GeneralBlock',
  slot: 'Content',
  shouldForwardProp: (prop: string) =>
    prop !== 'maxWidth' &&
    prop !== 'fullHeight' &&
    prop !== 'variant' &&
    prop !== 'borderStyle' &&
    prop !== 'bgColor'
})({
  position: 'relative',
  boxSizing: 'border-box'
});

export default function BlockContentRoot({ children, ...rest }: Props) {
  const { blockProps: { contentStyle } = {} } = useBlock();

  return (
    <BlockContent data-testid="blockContent" {...contentStyle} {...rest}>
      {children}
      <BlockDivider variant={contentStyle?.dividerVariant} />
    </BlockContent>
  );
}
