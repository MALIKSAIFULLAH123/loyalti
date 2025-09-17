/**
 * @type: ui
 * name: giphy.control.attachGif
 * chunkName: comment
 */

import loadable from '@loadable/component';
import { useGlobal } from '@metafox/framework';
import { AttachGifButtonProps } from '@metafox/giphy';
import { ClickOutsideListener, Tooltip } from '@metafox/ui';
import { IconButton, Popper, styled } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import ImageSvg from './ImageSvg';

const GifPicker = loadable(
  () =>
    import(
      /* webpackChunkName: "GifPicker" */
      './GifPicker/GifPicker'
    )
);

const name = 'AttachGifButton';

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

export default function AttachGifButton({
  title,
  icon = 'ico-comment-quote-o',
  onGifClick
}: AttachGifButtonProps) {
  const { i18n } = useGlobal();
  const altTitle = title ?? i18n.formatMessage({ id: 'post_a_gif' });
  const anchorRef = React.useRef();
  const popperRef = React.useRef();
  const [open, setOpen] = React.useState<boolean>(false);
  const onClickAway = React.useCallback(() => {
    setOpen(false);
  }, []);

  const togglePopper = React.useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  const handleGifClick = React.useCallback(
    (value: string) => {
      if (onGifClick) {
        onGifClick(value);
      }
    },
    [onGifClick]
  );

  return (
    <>
      <Tooltip title={altTitle}>
        <AttachBtn
          size="smaller"
          onClick={togglePopper}
          ref={anchorRef}
          data-testid={camelCase('buttonAttachGif')}
          role="button"
        >
          <ImageSvg />
        </AttachBtn>
      </Tooltip>
      {open ? (
        <ClickOutsideListener onClickAway={onClickAway}>
          <Popper
            ref={popperRef}
            anchorEl={anchorRef.current}
            style={{ zIndex: 1300 }}
            open={open && Boolean(anchorRef.current)}
            placement={'top-end'}
          >
            <GifPicker onClickItem={handleGifClick} />
          </Popper>
        </ClickOutsideListener>
      ) : null}
    </>
  );
}
