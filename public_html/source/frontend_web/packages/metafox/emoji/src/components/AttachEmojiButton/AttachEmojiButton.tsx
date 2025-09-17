import loadable from '@loadable/component';
import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener } from '@metafox/ui';
import { Paper, Popper, PopperProps } from '@mui/material';
import React from 'react';
import { AttachEmojiButtonProps } from '../../types';

const EmojiPicker = loadable(
  () =>
    import(
      /* webpackChunkName: "EmojiPicker" */
      '../EmojiPicker'
    )
);

export default function AttachEmojiButton({
  onEmojiClick,
  multiple = true,
  disabled,
  scrollRef,
  scrollClose,
  placement = 'top',
  disablePortal = false,
  label = 'insert_an_emoji',
  control: Control
}: AttachEmojiButtonProps) {
  const { i18n, useIsMobile } = useGlobal();
  const title = i18n.formatMessage({ id: label });
  const [anchorEl, setAnchorEl] = React.useState<PopperProps['anchorEl']>(null);
  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);

  const isMobile = useIsMobile();

  const onClickAway = React.useCallback(() => {
    setOpen(false);
  }, []);

  const togglePopper = React.useCallback((evt: React.MouseEvent) => {
    setOpen(prev => !prev);
    setAnchorEl(anchorEl ? null : evt.currentTarget);
  }, []);

  const handleEmojiClick = React.useCallback(
    (unicode: string, shortcut) => {
      if (onEmojiClick) {
        onEmojiClick(unicode, shortcut);
      }

      if (!multiple) {
        setOpen(false);
      }
    },
    [multiple, onEmojiClick]
  );

  React.useEffect(() => {
    if (open && scrollRef && scrollRef.current && scrollClose) {
      const off = () => setOpen(false);

      scrollRef.current.addEventListener('scroll', off);

      return () => {
        scrollRef?.current.removeEventListener('scroll', off);
      };
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  return (
    <>
      <ClickOutsideListener excludeRef={popperRef} onClickAway={onClickAway}>
        <Control
          onClick={togglePopper}
          disabled={disabled}
          title={title}
          data-testid="buttonAttachEmoji"
          testid="buttonAttachEmoji"
          icon="ico-smile-o"
        />
      </ClickOutsideListener>
      <Popper
        ref={popperRef}
        open={open}
        anchorEl={anchorEl}
        placement={isMobile ? 'top-end' : placement}
        disablePortal={disablePortal}
        role="presentation"
        sx={{
          zIndex: 1300
          // left: '-16px !important'
        }}
      >
        <Paper
          data-testid="dialogEmojiPicker"
          sx={{
            width: 290,
            height: 'auto',
            display: 'flex',
            flexDirection: 'column',
            bgcolor: 'background.paper',
            paddingTop: 1
          }}
        >
          <EmojiPicker onEmojiClick={handleEmojiClick} />
        </Paper>
      </Popper>
    </>
  );
}
