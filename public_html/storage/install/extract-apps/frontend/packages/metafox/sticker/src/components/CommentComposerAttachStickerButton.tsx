/**
 * @type: ui
 * name: commentComposer.control.attachSticker
 * chunkName: comment
 */
import loadable from '@loadable/component';
import { useGlobal } from '@metafox/framework';
import { AttachStickerButtonProps } from '@metafox/sticker';
import { ClickOutsideListener, LineIcon, Tooltip } from '@metafox/ui';
import { IconButton, Popper, styled } from '@mui/material';
import React from 'react';

const StickerPicker = loadable(
  () =>
    import(
      /* webpackChunkName: "StickerPicker" */
      './StickerPicker'
    )
);

const name = 'AttachStickerButton';

const AttachBtn = styled(IconButton, { name, slot: 'attachBtn' })(
  ({ theme }) => ({
    padding: 0,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: theme.spacing(3.5),
    height: theme.spacing(3.5),
    minWidth: theme.spacing(3.5),
    color: `${theme.palette.text.secondary} !important`
  })
);

const AttachBtnIcon = styled(LineIcon, { name, slot: 'attachBtnIcon' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(14)
  })
);

export default function AttachStickerButton({
  title,
  icon = 'ico-sticker-o',
  multiple,
  onStickerClick
}: AttachStickerButtonProps) {
  const { i18n } = useGlobal();
  const altTitle = title ?? i18n.formatMessage({ id: 'post_a_sticker' });
  const anchorRef = React.useRef();
  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);
  const onClickAway = React.useCallback(() => {
    setOpen(false);
  }, []);

  const togglePopper = React.useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  const handleStickerClick = React.useCallback(
    (value: number) => {
      if (!multiple) {
        setOpen(false);
      }

      if (onStickerClick) {
        onStickerClick(value);
      }
    },
    [multiple, onStickerClick]
  );

  return (
    <>
      <Tooltip title={altTitle}>
        <AttachBtn
          size="smaller"
          onClick={togglePopper}
          ref={anchorRef}
          data-testid="buttonAttachSticker"
          role="button"
        >
          <AttachBtnIcon icon={icon} />
        </AttachBtn>
      </Tooltip>
      {open ? (
        <ClickOutsideListener onClickAway={onClickAway}>
          <Popper
            ref={popperRef}
            anchorEl={anchorRef.current}
            style={{ zIndex: 1300 }}
            open={open && Boolean(anchorRef.current)}
            placement="top-end"
          >
            <StickerPicker
              multiple={false}
              onStickerClick={handleStickerClick}
            />
          </Popper>
        </ClickOutsideListener>
      ) : null}
    </>
  );
}
