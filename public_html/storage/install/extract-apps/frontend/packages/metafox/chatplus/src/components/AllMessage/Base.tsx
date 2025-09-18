import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

export interface Props extends BlockViewProps {}

const name = 'AllMessage';
const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: (props, styles) => [styles.root]
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  display: 'flex',
  width: '100%',
  height: '100%'
}));

const BuddyWrapStyled = styled(Box, {
  name,
  slot: 'buddy-wrap',
  overridesResolver: (props, styles) => [styles.buddyWrap]
})(({ theme }) => ({
  width: '360px'
}));

const RoomWrapStyled = styled(Box, {
  name,
  slot: 'room-wrap'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0
}));

export default function Base(props: Props) {
  const { jsxBackend } = useGlobal();
  const Buddy = jsxBackend.get('chatplus.block.buddy');
  const Room = jsxBackend.get('chatplus.block.chatroom');

  return (
    <Root data-testid={camelCase('block page allMessage')}>
      <BuddyWrapStyled>
        <Buddy />
      </BuddyWrapStyled>
      <RoomWrapStyled>
        <Room />
      </RoomWrapStyled>
    </Root>
  );
}
