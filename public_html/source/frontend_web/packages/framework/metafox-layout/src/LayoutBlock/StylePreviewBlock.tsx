/**
 * @type: ui
 * name: layout.block.StylePreviewBlock
 */
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import Block from './BlockContainer';
import BlockContent from './BlockContent';
import BlockFooter from './BlockFooter';
import BlockHeader from './BlockHeader';

const Root = styled(Box, {
  name: 'StylePreviewBlock',
  slot: 'root',
  shouldForwardProp: prop => prop !== 'showFrame'
})<{ showFrame?: boolean }>(({ showFrame }) => ({
  position: 'relative',
  ...(showFrame && {
    border: '1px solid'
  })
}));
const SampleContent = styled(Box, {
  name: 'StylePreviewBlock',
  slot: 'sampleContent'
})({
  height: 200,
  width: '100%',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: '#ddd'
});
const SampleFooter = styled(Box, {
  name: 'StylePreviewBlock',
  slot: 'sampleFooter'
})({
  height: 40,
  width: '100%',
  alignItems: 'center',
  justifyContent: 'center'
});

export default function StylePreviewBlock({ showFrame, title = 'Title' }) {
  return (
    <Root showFrame={showFrame}>
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <SampleContent>content</SampleContent>
        </BlockContent>
        <BlockFooter>
          <SampleFooter>footer</SampleFooter>
        </BlockFooter>
      </Block>
    </Root>
  );
}
