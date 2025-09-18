import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
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

const BuddyMobileStyled = styled(Box, {
  name,
  slot: 'buddy-wrap',
  overridesResolver: (props, styles) => [styles.buddyWrap],
  shouldForwardProp: props => props !== 'isNoShow'
})<{ isNoShow: boolean }>(({ theme, isNoShow }) => ({
  width: '100%',
  ...(isNoShow && {
    display: 'none'
  })
}));

const RoomMobileStyled = styled(Box, {
  name,
  slot: 'room-wrap',
  shouldForwardProp: props => props !== 'isNoShow'
})<{ isNoShow: boolean }>(({ theme, isNoShow }) => ({
  width: '100%',
  ...(isNoShow && {
    display: 'none'
  })
}));

export default function Base(props: Props) {
  const { jsxBackend, usePageParams } = useGlobal();
  const Buddy = jsxBackend.get('chat.block.buddy');
  const Room = jsxBackend.get('chat.block.chatroom');
  const pageParams = usePageParams();

  const { rid } = pageParams;

  return (
    <Root>
      <BuddyMobileStyled isNoShow={Boolean(rid)}>
        <Buddy />
      </BuddyMobileStyled>
      <RoomMobileStyled isNoShow={Boolean(!rid)}>
        <Room />
      </RoomMobileStyled>
    </Root>
  );
}
