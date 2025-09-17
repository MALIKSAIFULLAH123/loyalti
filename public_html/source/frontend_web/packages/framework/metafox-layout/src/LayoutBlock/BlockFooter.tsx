/**
 * @type: ui
 * name: ui.block.default.footer
 */

import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import BlockDivider from './BlockDivider';
type Props = {
  children?: JSX.Element;
};

const BlockFooter = styled(Box, {
  name: 'GeneralBlock',
  slot: 'footer',
  shouldForwardProp: (prop: string) =>
    prop !== 'maxWidth' && prop !== 'fullHeight' && prop !== 'variant'
})(({ theme }) => ({
  position: 'relative',
  boxSizing: 'border-box',
  overflow: 'hidden'
}));

export default function BlockFooterRoot(props: Props) {
  const { noFooter, blockProps: { footerStyle } = {} } = useBlock();

  if (noFooter || !props.children) return null;

  return (
    <>
      <BlockDivider variant={footerStyle?.dividerVariant} />
      <BlockFooter data-testid="blockFooter" {...footerStyle} {...props} />
    </>
  );
}
