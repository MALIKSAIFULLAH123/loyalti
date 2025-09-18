import { NEW_CHAT_ROOM } from '@metafox/chatplus/constants';
import {
  getGroupChatsSelector,
  getOpenChatRooms
} from '@metafox/chatplus/selectors';
import { GlobalState, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton, Tooltip, styled } from '@mui/material';
import React from 'react';
import { useSelector } from 'react-redux';
import BuddyItem from './BuddyItem';
import MoreItems from './MoreItems';
import MoreOption from './MoreOptions';
import useSpotlight from '@metafox/chatplus/hooks/useSpotlight';
import { camelCase, isEmpty } from 'lodash';

const name = 'BuddyPanel';

const NewMessage = styled('div')(({ theme }) => ({
  position: 'relative',
  width: '48px',
  height: '48px',
  backgroundColor: '#fff',
  borderRadius: '50%',
  marginTop: theme.spacing(1),
  cursor: 'pointer',
  border:
    theme.palette.mode === 'light' ? theme.mixins.border('secondary') : 'none',
  boxShadow: theme.shadows[4],
  '&:hover': {
    '.closeButtonStyled': {
      opacity: 1
    }
  }
}));

const AddMessageIcon = styled(LineIcon)(({ theme }) => ({
  fontSize: theme.spacing(3),
  color: theme.palette.primary.main,
  position: 'absolute',
  top: '55%',
  left: '50%',
  transform: 'translate(-50%, -50%)'
}));

const Block = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  '&:hover': {
    '.moreOptionStyled': {
      visibility: 'visible'
    }
  }
}));

const CloseButtonStyled = styled(IconButton, { name })(({ theme }) => ({
  right: theme.spacing(-1),
  top: 0,
  position: 'absolute',
  opacity: 0,
  width: theme.spacing(2.75),
  height: theme.spacing(2.75),
  border:
    theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['50']
      : theme.palette.background.paper,
  borderRadius: '50%',
  zIndex: 999,
  '& span.ico': {
    fontSize: theme.spacing(1.5),
    ...(theme.palette.mode === 'dark' && {
      color: theme.palette.grey['A700']
    })
  },
  '&:hover': {
    backgroundColor:
      theme.palette.mode === 'dark'
        ? `${theme.palette.grey['50']} !important`
        : `${theme.palette.grey['300']} !important`
  }
}));

const BuddyItemStyled = styled(BuddyItem)(({ theme }) => ({}));

const MoreOptionStyled = styled('div', { name, slot: 'MoreOptionStyled' })(
  ({ theme }) => ({
    visibility: 'hidden'
  })
);

export default function BuddyPanel() {
  const { dispatch, i18n } = useGlobal();
  const limitDisplay = 5;
  const { searchText } = useSpotlight();

  const { values, closeIconMsg } = useSelector((state: GlobalState) =>
    getOpenChatRooms(state)
  );

  const { chatBot } = useSelector<GlobalState, any>(state =>
    getGroupChatsSelector(state, null, true)
  );

  const openRooms = [...values]
    .reverse()
    .filter(item => item?.rid !== chatBot?.id);

  const bubbyList = openRooms.filter(item => item.collapsed);

  const isAllCollapsed = openRooms.length === bubbyList.length;

  const togglePanel = React.useCallback(() => {
    dispatch({
      type: 'chatplus/openRooms/addRoomToChatDock',
      payload: { rid: NEW_CHAT_ROOM }
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const dataBubbyList = React.useMemo(
    () => bubbyList.slice(0, limitDisplay),
    [bubbyList]
  );

  const handleCloseIconMsg = React.useCallback(() => {
    dispatch({
      type: 'chatplus/closeIconMsg'
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (closeIconMsg) return null;

  return (
    <Block data-testid={camelCase('Block Buddy Panel')}>
      {bubbyList && bubbyList.length > 0 && (
        <MoreOptionStyled className="moreOptionStyled">
          <MoreOption isAllCollapsed={isAllCollapsed} />
        </MoreOptionStyled>
      )}
      {dataBubbyList.map((item, idx) => (
        <BuddyItemStyled item={item} key={item.rid} />
      ))}
      {bubbyList.length > limitDisplay && (
        <MoreItems buddyList={bubbyList} limitDisplay={limitDisplay} />
      )}
      {isEmpty(searchText) && chatBot?.id ? (
        <BuddyItemStyled item={{ rid: chatBot?.id }} />
      ) : null}
      <Tooltip title={i18n.formatMessage({ id: 'new_conversation' })}>
        <NewMessage
          data-testid={camelCase('New Message')}
          onClick={togglePanel}
        >
          <AddMessageIcon icon="ico-comment-plus-o" />
          <CloseButtonStyled
            onClickCapture={handleCloseIconMsg}
            className="closeButtonStyled"
            aria-label="action"
            size="small"
            color="primary"
            variant="white-contained"
          >
            <LineIcon icon={'ico-close'} />
          </CloseButtonStyled>
        </NewMessage>
      </Tooltip>
    </Block>
  );
}
