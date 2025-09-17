import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';
import icons from './Icons';

export interface Props {
  onIconClick?: (value: string) => void;
}
const StyledItem = styled('div', {
  slot: 'IconPicker',
  name: 'Item'
})({
  display: 'inline-block',
  width: 40,
  height: 40,
  textAlign: 'center'
});

export default function IconPicker({ onIconClick }: Props) {
  const scrollRef = React.useRef<HTMLDivElement>();

  return (
    <Box sx={{ height: 250 }}>
      <ScrollContainer autoHide autoHeight autoHeightMax={232} ref={scrollRef}>
        {icons.map(value => (
          <StyledItem onClick={() => onIconClick(value)} key={value}>
            <Tooltip title={value} placement="bottom">
              <LineIcon icon={value} sx={{ fontSize: 20, cursor: 'pointer' }} />
            </Tooltip>
          </StyledItem>
        ))}
      </ScrollContainer>
    </Box>
  );
}
