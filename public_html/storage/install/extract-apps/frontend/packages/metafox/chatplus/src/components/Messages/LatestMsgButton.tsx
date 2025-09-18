import { Box, Button, IconButton, styled } from '@mui/material';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { isEmpty } from 'lodash';
import { MODE_UN_SEARCH } from '@metafox/chatplus/constants';

const name = 'ChatPlusLatestMsgButton';

const Wrapper = styled(Box, {
  name,
  slot: 'wrapperStyled',
  shouldForwardProp: props => props !== 'reactMode'
})<{ reactMode?: string }>(({ theme, reactMode }) => ({
  position: 'absolute',
  bottom: theme.spacing(1.5),
  display: 'inline-flex',
  left: '50%',
  transform: 'translate(-50%)',
  zIndex: theme.zIndex.tooltip + 10,
  width: 'fit-content',
  '& button': {
    ...(theme.palette.mode === 'light'
      ? { boxShadow: theme.shadows[3] }
      : { boxShadow: 'none' }),
    '&:hover': {
      backgroundColor:
        theme.palette.mode === 'dark'
          ? theme.palette.action.selected
          : theme.palette.background.default
    }
  },
  ...(reactMode !== 'no_react' && {
    display: 'none'
  })
}));

const ButtonStyled = styled(Button)(({ theme }) => ({
  boxShadow: 'none',
  fontWeight: '400 !important',
  fontSize: '15px !important',
  borderRadius: '999px',
  padding: '16px 24px !important'
}));

const IconButtonStyled = styled(IconButton)(({ theme }) => ({
  boxShadow: 'none',
  fontWeight: '400 !important',
  fontSize: '15px !important',
  borderRadius: '999px',
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.action.selected
      : theme.palette.background.default
}));

const LatestMsgButton = ({
  onClick = () => {},
  text = 'latest_messages',
  reactMode = 'no_react',
  showText = true,
  refMessage,
  setShowLatestMsg,
  searchMessages,
  rid
}: any) => {
  const { i18n, dispatch } = useGlobal();

  const handleClick = () => {
    if (!isEmpty(searchMessages)) {
      dispatch({
        type: 'chatplus/room/modeSearch',
        payload: { rid, mode: MODE_UN_SEARCH },
        meta: {
          onSuccess: () => {
            if (refMessage?.current) {
              refMessage.current.scrollToBottom();
              setShowLatestMsg(false);
            }
          }
        }
      });

      return;
    }

    if (refMessage?.current) {
      refMessage.current.scrollToBottom();
      setShowLatestMsg(false);
    }
  };

  return (
    <Wrapper reactMode={reactMode}>
      {showText ? (
        <ButtonStyled
          disableRipple
          disableFocusRipple
          data-testid="buttonFetchNewFeed"
          role="button"
          id="buttonFetchNewFeed"
          autoFocus
          color="default"
          size="smaller"
          variant="contained"
          onClick={handleClick}
          endIcon={
            <LineIcon
              sx={{ fontSize: '16px !important' }}
              icon="ico-arrow-down"
            />
          }
        >
          {i18n.formatMessage({ id: text })}
        </ButtonStyled>
      ) : (
        <IconButtonStyled color="default" onClick={handleClick}>
          <LineIcon
            sx={{ fontSize: '16px !important' }}
            icon="ico-arrow-down"
          />
        </IconButtonStyled>
      )}
    </Wrapper>
  );
};

export default LatestMsgButton;
