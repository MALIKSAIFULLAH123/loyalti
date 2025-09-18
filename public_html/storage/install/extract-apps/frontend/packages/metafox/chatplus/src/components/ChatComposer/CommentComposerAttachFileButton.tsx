/**
 * @type: ui
 * name: commentComposer.control.attachFile
 * chunkName: comment
 */

import { useGlobal } from '@metafox/framework';
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import { Paper, styled, Tooltip } from '@mui/material';
import Popper from '@mui/material/Popper';
import { camelCase } from 'lodash';
import React from 'react';

const WrapperButtonIcon = styled('div')(({ theme }) => ({
  fontSize: theme.spacing(1.875),
  padding: 0,
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: '28px',
  height: '28px',
  minWidth: '28px',
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['700']
      : theme.palette.text.primary,
  cursor: 'pointer'
}));
const Item = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.25, 1.75),
  fontSize: theme.mixins.pxToRem(13),
  lineHeight: theme.mixins.pxToRem(16),
  fontWeight: theme.typography.fontWeightMedium,
  '& .ico': {
    marginRight: theme.spacing(1)
  }
}));

function AttachEmojiToStatusComposer({
  rid,
  previewRef,
  filesUploadRef,
  placement = 'top-end',
  disablePortal = false
}: any) {
  const { i18n, useIsMobile, useTheme } = useGlobal();
  const inputRef = React.useRef<HTMLInputElement>();
  const fileUploadRef = React.useRef<HTMLInputElement>();
  const [openTooltip, setOpenTooltip] = React.useState(false);

  const isMobile = useIsMobile(true);

  const theme = useTheme();

  const onChangeImage = () => {
    if (!inputRef.current.files.length) return;

    if (previewRef) {
      const initData = previewRef.current?.listFiles() || [];
      previewRef.current?.attachFiles([...initData, ...inputRef.current.files]);
      previewRef.current?.typeUpload('image');
    }

    if (filesUploadRef) {
      const initData = filesUploadRef.current?.getPreviewFiles() || [];

      filesUploadRef.current?.attachFiles([
        ...initData,
        ...inputRef.current.files
      ]);
    }

    // clear value of inputRef when selected done
    if (inputRef?.current) {
      inputRef.current.value = null;
    }
  };

  const fileUploadChanged = () => {
    if (!fileUploadRef.current.files.length) return;

    if (previewRef) {
      const initData = previewRef.current?.listFiles() || [];

      previewRef.current?.attachFiles([
        ...initData,
        ...fileUploadRef.current.files
      ]);
      previewRef.current?.typeUpload('file');
    }

    if (filesUploadRef) {
      const initData = filesUploadRef.current?.getPreviewFiles() || [];

      filesUploadRef.current?.attachFiles([
        ...initData,
        ...fileUploadRef.current.files
      ]);
    }

    // clear value of inputRef when selected done
    if (fileUploadRef?.current) {
      fileUploadRef.current.value = null;
    }
  };

  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);

  const handleClick = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(anchorEl ? null : event.currentTarget);
  };

  const handleMouseLeave = () => {
    setOpenTooltip(false);
  };

  const handleMouseEnter = () => {
    if (!anchorEl) setOpenTooltip(true);
  };

  const open = Boolean(anchorEl);
  const id = open ? 'simple-popper' : undefined;

  const attachImages = React.useCallback(() => {
    inputRef.current.click();
  }, []);

  const attachFile = React.useCallback(() => {
    fileUploadRef.current.click();
  }, []);

  const items = React.useMemo(() => {
    return [
      {
        label: i18n.formatMessage({ id: 'chatplus_send_photo' }),
        icon: 'ico-photo',
        onClick: attachImages,
        condition: true
      },
      {
        label: i18n.formatMessage({ id: 'chatplus_attach_files' }),
        icon: 'ico-paperclip-alt',
        onClick: attachFile,
        condition: true
      }
    ];
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [attachImages, attachFile]);

  return (
    <div data-testid={camelCase('button attachment composer')}>
      <Tooltip
        open={isMobile ? false : openTooltip}
        title={i18n.formatMessage({ id: 'open_more_options' })}
        placement="top"
      >
        <WrapperButtonIcon
          onClick={handleClick}
          onMouseLeave={handleMouseLeave}
          onMouseEnter={handleMouseEnter}
        >
          <LineIcon icon="ico-plus" />
        </WrapperButtonIcon>
      </Tooltip>
      {open ? (
        <ClickOutsideListener onClickAway={handleClick}>
          <Popper
            id={id}
            open={open}
            placement={placement}
            anchorEl={anchorEl}
            disablePortal={disablePortal}
            style={{ zIndex: theme.zIndex.modal }}
          >
            <Paper style={{ padding: '4px 0' }}>
              {items.map((item, index) => (
                <Item key={index} role={'button'} onClick={item.onClick}>
                  {item.icon ? <LineIcon icon={item.icon} /> : null}
                  {item.label ? <span>{item.label}</span> : null}
                </Item>
              ))}
            </Paper>
          </Popper>
        </ClickOutsideListener>
      ) : null}
      <input
        data-testid="inputAttachPhoto"
        onChange={onChangeImage}
        multiple
        ref={inputRef}
        style={{ display: 'none' }}
        type="file"
        accept="image/*"
      />
      <input
        style={{ display: 'none' }}
        type="file"
        multiple
        ref={fileUploadRef}
        onChange={fileUploadChanged}
      />
    </div>
  );
}

export default React.memo(
  AttachEmojiToStatusComposer,
  (prev, next) => prev.rid === next.rid
);
