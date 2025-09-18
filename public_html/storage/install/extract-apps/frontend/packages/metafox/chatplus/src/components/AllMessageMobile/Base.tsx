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
  shouldForwardProp: props => props !== 'isShow'
})<{ isShow: boolean }>(({ theme, isShow }) => ({
  width: '100%',
  display: 'none',
  ...(isShow && {
    display: 'block'
  })
}));

const RoomMobileStyled = styled(Box, {
  name,
  slot: 'room-wrap',
  shouldForwardProp: props => props !== 'isShow'
})<{ isShow: boolean }>(({ theme, isShow }) => ({
  width: '100%',
  display: 'none',
  ...(isShow && {
    display: 'block'
  })
}));

export default function Base(props: Props) {
  const { jsxBackend, usePageParams } = useGlobal();
  const BuddyList = jsxBackend.get('chatplus.block.buddy');
  const RoomChat = jsxBackend.get('chatplus.block.chatroom');
  const pageParams = usePageParams();

  const { rid } = pageParams;

  return (
    <Root>
      <BuddyMobileStyled isShow={Boolean(!rid)}>
        <BuddyList />
      </BuddyMobileStyled>
      <RoomMobileStyled isShow={Boolean(rid)}>
        <RoomChat />
      </RoomMobileStyled>
    </Root>
  );
}
