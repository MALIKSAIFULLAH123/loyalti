/**
 * @type: ui
 * name: ui.block.default
 * chunkName: boot
 */
import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import BlockDivider from './BlockDivider';

type Props = {
  children?: any;
  testid?: string;
};

export type BlockWrapperProps = {
  children?: any;
  testid?: string;
  blockProps: any;
};

const GeneralBlock = styled(Box, {
  name: 'GeneralBlock',
  slot: 'Root',
  overridesResolver(props, styles) {
    return [styles.root];
  },
  shouldForwardProp: (prop: string) =>
    prop !== 'maxWidth' &&
    prop !== 'fullHeight' &&
    prop !== 'variant' &&
    prop !== 'dividerVariant' &&
    prop !== 'bgColor' &&
    prop !== 'sx' &&
    prop !== 'borderStyle' &&
    prop !== 'blockShadow'
})<{ sx?: any; blockShadow?: boolean }>(({ theme, sx, blockShadow }) => ({
  position: 'relative',
  boxShadow:
    blockShadow ??
    (sx?.bgcolor && !['0', 'transparent'].includes(sx?.bgcolor)
      ? theme?.blockShadow
      : 'none'),
  ...(sx?.maxWidth &&
    ['xs1', 'xs2', 'xs3', 'sm1'].includes(sx?.maxWidth) && {
      maxWidth: `${theme.layoutSlot.points[sx?.maxWidth]}px !important`
    })
}));

export default function BlockContainer({ children, testid }: Props) {
  const { blockProps: { blockStyle } = {}, testid: altId } = useBlock();

  if (!children) return null;

  return (
    <GeneralBlock {...blockStyle} role="block" data-testid={testid ?? altId}>
      {children}
      <BlockDivider variant={blockStyle?.dividerVariant} />
    </GeneralBlock>
  );
}
