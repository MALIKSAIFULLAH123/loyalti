import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import {
  Box,
  Button,
  IconButton,
  Popper,
  styled,
  Tooltip
} from '@mui/material';
import React from 'react';

const name = 'MoreOptions';
const ActionButtonStyled = styled(IconButton, { name })(({ theme }) => ({
  width: '36px',
  height: '36px',
  border:
    theme.palette.mode === 'dark' ? 'none' : theme.mixins.border('secondary'),
  boxShadow: theme.shadows[2],
  backgroundColor:
    theme.palette.mode === 'dark'
      ? theme.palette.grey['50']
      : theme.palette.background.paper,
  borderRadius: '50%',
  marginTop: theme.spacing(1),
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

const ItemViewStyled = styled('div', {
  name,
  shouldForwardProp: props => props !== 'open'
})<{ open?: boolean }>(({ theme, open }) => ({
  position: 'relative',
  cursor: 'pointer',
  ...(open && {
    visibility: 'visible'
  })
}));

const WrapperPopper = styled('div', { name })(({ theme }) => ({
  width: '350px',
  border: theme.mixins.border('secondary'),
  boxShadow: theme.shadows[2],
  padding: theme.spacing(0.5),
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.spacing(0.75),
  zIndex: 999
}));

const ButtonStyled = styled(Button, { name, slot: 'ButtonStyled' })(
  ({ theme }) => ({
    ...theme.typography.body1,
    width: '100%',
    padding: theme.spacing(0, 1.5),
    justifyContent: 'flex-start',
    backgroundColor: theme.palette.background.paper,
    '& .MuiButton-startIcon': {
      fontSize: theme.spacing(2.5)
    }
  })
);

const StyledTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    ...theme.typography.h6,
    backgroundColor: 'white',
    color: 'black',
    border: theme.mixins.border('secondary'),
    boxShadow: theme.shadows[2],
    padding: theme.spacing(1),
    zIndex: 1000
  },
  '& .MuiTooltip-arrow': {
    '&::before': {
      backgroundColor: 'white',
      border: theme.mixins.border('secondary'),
      boxShadow: theme.shadows[2]
    }
  }
}));

interface Props {
  isAllCollapsed?: boolean;
}

export default function MoreOptions({ isAllCollapsed }: Props) {
  const { dispatch, i18n } = useGlobal();

  const [open, setOpen] = React.useState<boolean>(false);
  const toggleOpen = () => setOpen((prev: boolean) => !prev);
  const anchorRef = React.useRef<HTMLDivElement>();

  const handleClickAway = () => {
    setOpen(false);
  };

  const toggleAllChatRoom = React.useCallback(
    () => {
      setOpen(false);
      dispatch({
        type: 'chat/openRooms/collapsedAllPanel'
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );
  const closeAllChatRoom = React.useCallback(
    () => {
      setOpen(false);
      dispatch({
        type: 'chat/closeAllPanel'
      });
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  return (
    <ClickOutsideListener onClickAway={handleClickAway}>
      <ItemViewStyled open={open} ref={anchorRef}>
        <StyledTooltip
          title={i18n.formatMessage({ id: 'more_options' })}
          placement="right"
        >
          <ActionButtonStyled
            onClick={toggleOpen}
            className="actionButtonStyled"
            aria-label="action"
            size="small"
            color="primary"
            variant="white-contained"
          >
            <LineIcon icon={'ico-dottedmore'} />
          </ActionButtonStyled>
        </StyledTooltip>
        <Popper
          id="more_options_chat"
          open={open}
          anchorEl={anchorRef.current}
          disablePortal
          placement="top-end"
        >
          <WrapperPopper>
            <Box>
              <ButtonStyled
                onClick={closeAllChatRoom}
                color="default"
                startIcon={<LineIcon icon="ico-close-circle-o" />}
              >
                {i18n.formatMessage({ id: 'close_all_boxchats' })}
              </ButtonStyled>
            </Box>
            {isAllCollapsed ? null : (
              <Box mt={0.5}>
                <ButtonStyled
                  onClick={toggleAllChatRoom}
                  color="default"
                  startIcon={<LineIcon icon="ico-minus-circle-o" />}
                >
                  {i18n.formatMessage({ id: 'minimize_all_boxchats_open' })}
                </ButtonStyled>
              </Box>
            )}
          </WrapperPopper>
        </Popper>
      </ItemViewStyled>
    </ClickOutsideListener>
  );
}
