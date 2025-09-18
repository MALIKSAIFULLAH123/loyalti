import { AttachEmojiButtonProps } from '@metafox/emoji';
import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener } from '@metafox/ui';
import { Paper, Popper, PopperProps, styled } from '@mui/material';
import React from 'react';
import ReactListPopover from './ReactListPopover';
import { isFunction } from 'lodash';
import { IPropClickAction } from '@metafox/chatplus/types';

const PaperStyled = styled(Paper)(({ theme }) => ({
  margin: 0,
  padding: theme.spacing(0, 0.5),
  maxHeight: '48px',
  display: 'flex',
  alignItems: 'center',
  whiteSpace: 'nowrap'
}));

const TypeAction = 'emoji';

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
  unsetReaction,
  showHover,
  onClickAction,
  showActionRef
}: AttachEmojiButtonProps & {
  identity: string;
  unsetReaction: any;
  showHover?: boolean;
  onClickAction?: (obj: IPropClickAction) => void;
  showActionRef?: any;
}) {
  const { i18n } = useGlobal();
  const title = i18n.formatMessage({ id: label });
  const [anchorEl, setAnchorEl] = React.useState<PopperProps['anchorEl']>(null);

  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);

  const handleClose = React.useCallback(() => {
    setOpen(false);

    if (
      showActionRef?.current &&
      showActionRef?.current?.id === identity &&
      !showActionRef?.current?.showHover &&
      showActionRef?.current?.type === TypeAction &&
      isFunction(onClickAction)
    ) {
      onClickAction({ identity, type: TypeAction });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showActionRef?.current]);

  const onClickAway = React.useCallback(() => {
    handleClose();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showActionRef?.current, handleClose]);

  const togglePopper = React.useCallback((evt: React.MouseEvent) => {
    setOpen(prev => !prev);
    setAnchorEl(anchorEl ? null : evt.currentTarget);
    isFunction(onClickAction) && onClickAction({ identity, type: TypeAction });
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
      const off = () => handleClose();

      scrollRef.current.addEventListener('scroll', off);

      return () => {
        scrollRef?.current.removeEventListener('scroll', off);
      };
    }
    // eslint-disable-next-line
  }, [open]);

  return (
    <>
      <Control
        showHover={showHover}
        onClick={togglePopper}
        disabled={disabled}
        title={title}
        data-testid="buttonAttachEmoji"
        icon="ico-smile-o"
      />
      {open ? (
        <ClickOutsideListener excludeRef={popperRef} onClickAway={onClickAway}>
          <Popper
            ref={popperRef}
            open={open}
            anchorEl={anchorEl}
            placement={placement}
            role="presentation"
            style={{ zIndex: 1300 }}
            popperOptions={{
              strategy: 'fixed'
            }}
            variant="hidden-outview"
          >
            <PaperStyled data-testid="dialogEmojiPicker">
              <ReactListPopover
                onEmojiClick={handleEmojiClick}
                unsetReaction={handleUnsetReactionClick}
                identity={identity}
              />
            </PaperStyled>
          </Popper>
        </ClickOutsideListener>
      ) : null}
    </>
  );
}
