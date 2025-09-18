/**
 * @type: ui
 * name: chatplus.ui.FilterMessages
 * chunkName: chatplusUI
 */

import {
  useMessageFilterItem,
  usePublicSettings,
  useRoomItem,
  useSessionUser,
  useSubscriptionItem
} from '@metafox/chatplus/hooks';
import { RefOf, useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { ClickOutsideListener } from '@metafox/ui';
import {
  Box,
  Paper,
  Popper,
  PopperProps,
  styled,
  Typography
} from '@mui/material';
import React from 'react';
import MessageFilterPopper from '../../MsgFilterPopper/MessageFilterPopper';
import { TypeActionPinStar } from './Header';

const TitleHeader = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'flex-end'
}));
const Header = styled(Box, {
  shouldForwardProp: props => props !== 'noContent'
})<{ noContent?: boolean }>(({ theme, noContent }) => ({
  padding: theme.spacing(1.5, 2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',

  ...(!noContent && {
    borderBottom: '1px solid',
    borderColor: theme.palette.border?.secondary
  })
}));

export default function MessagesPopper({
  anchorRef,
  open,
  placement,
  type,
  closePopover
}: PopperProps & {
  anchorRef: RefOf<HTMLDivElement>;
  type: TypeActionPinStar;
  closePopover: any;
}) {
  const { i18n, usePageParams, dispatch } = useGlobal();

  const pageParams = usePageParams();

  const { rid } = pageParams;

  const messageFilter = useMessageFilterItem(rid);

  const user = useSessionUser();
  const subscription = useSubscriptionItem(rid);
  const room = useRoomItem(rid);
  const settings = usePublicSettings();

  const archived = !!subscription?.archived;

  const titleHeader = type === 'pin' ? 'pinned_messages' : 'starred_messages';
  const noContent =
    type === 'pin' ? 'no_pinned_messages' : 'no_starred_messages';

  React.useEffect(() => {
    if (type === 'pin') {
      dispatch({
        type: 'chatplus/room/pinnedMessages',
        payload: { roomId: room?.id, filter: 'pin' }
      });
    }

    if (type === 'star') {
      dispatch({
        type: 'chatplus/room/pinnedMessages',
        payload: { roomId: room?.id, filter: 'star' }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [type]);

  React.useEffect(() => {
    dispatch({
      type: 'chatplus/room/deleteMessagesFilter',
      payload: { identity: room?.id }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <ClickOutsideListener excludeRef={anchorRef} onClickAway={closePopover}>
      <Popper
        id="chatplus"
        data-testid="chatplus"
        anchorEl={anchorRef?.current}
        open={open}
        placement={placement || 'bottom-end'}
      >
        <Paper
          sx={{
            width: 320,
            overflow: 'hidden',
            userSelect: 'none'
          }}
        >
          <Header>
            <TitleHeader>
              <Typography variant="h4">
                {i18n.formatMessage({ id: titleHeader })}
              </Typography>
            </TitleHeader>
          </Header>
          <ScrollContainer
            autoHide
            autoHeight
            autoHeightMax={390}
            autoHeightMin={40}
          >
            <MessageFilterPopper
              user={user}
              items={messageFilter}
              archived={archived}
              room={room}
              isMobile={false}
              settings={settings}
              disableReact
              phraseNoContent={noContent}
              type={type}
              closePopover={closePopover}
            />
          </ScrollContainer>
        </Paper>
      </Popper>
    </ClickOutsideListener>
  );
}
