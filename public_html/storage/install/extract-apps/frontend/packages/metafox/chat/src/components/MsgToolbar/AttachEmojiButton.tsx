import { AttachEmojiButtonProps } from '@metafox/emoji';
import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener } from '@metafox/ui';
import { Paper, Popper, PopperProps, styled } from '@mui/material';
import React from 'react';
import ReactListPopover from './ReactListPopover';

const PaperStyled = styled(Paper)(({ theme }) => ({
  margin: 0,
  padding: theme.spacing(0, 0.5),
  maxHeight: '48px',
  display: 'flex',
  alignItems: 'center',
  whiteSpace: 'nowrap'
}));

export default function AttachEmojiButton({
  onEmojiClick,
  multiple = true,
  disabled,
  scrollRef,
  scrollClose,
  placement = 'top',
  label = 'react',
  control: Control,
  identity,
  unsetReaction
}: AttachEmojiButtonProps & { identity: string; unsetReaction: any }) {
  const { i18n } = useGlobal();
  const title = i18n.formatMessage({ id: label });
  const [anchorEl, setAnchorEl] = React.useState<PopperProps['anchorEl']>(null);

  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);

  const onClickAway = React.useCallback(() => {
    setOpen(false);
  }, []);

  const togglePopper = React.useCallback((evt: React.MouseEvent) => {
    setOpen(prev => !prev);
    setAnchorEl(anchorEl ? null : evt.currentTarget);
    // eslint-disable-next-line
  }, []);

  const handleEmojiClick = React.useCallback(
    (shortcut, unicode: string) => {
      if (onEmojiClick) {
        onEmojiClick(shortcut, unicode);
      }

      if (!multiple) {
        setOpen(false);
      }
    },
    [multiple, onEmojiClick]
  );

  const handleUnsetReactionClick = React.useCallback(
    shortcut => {
      if (unsetReaction) {
        unsetReaction(shortcut);
      }

      setOpen(false);
    },
    [unsetReaction]
  );

  React.useEffect(() => {
    if (open && scrollRef && scrollRef.current && scrollClose) {
      const off = () => setOpen(false);

      scrollRef.current.addEventListener('scroll', off);

      return () => {
        scrollRef?.current.removeEventListener('scroll', off);
      };
    }
    // eslint-disable-next-line
  }, [open]);

  return (
    <>
      <ClickOutsideListener excludeRef={popperRef} onClickAway={onClickAway}>
        <Control
          onClick={togglePopper}
          disabled={disabled}
          title={title}
          data-testid="buttonAttachEmoji"
          icon="ico-smile-o"
        />
      </ClickOutsideListener>
      <Popper
        ref={popperRef}
        open={open}
        anchorEl={anchorEl}
        placement={placement}
        role="presentation"
        style={{ zIndex: 5000 }}
      >
        <PaperStyled data-testid="dialogEmojiPicker">
          <ReactListPopover
            onEmojiClick={handleEmojiClick}
            unsetReaction={handleUnsetReactionClick}
            identity={identity}
          />
        </PaperStyled>
      </Popper>
    </>
  );
}
