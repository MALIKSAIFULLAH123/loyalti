/**
 * @type: ui
 * name: commentComposer.control.attachFile
 * chunkName: comment
 */

import useCheckImageSize from '@metafox/chat/hooks/useCheckImageSize';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
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
  cursor: 'pointer',
  '&:hover': {
    backgroundColor: theme.palette.action.hover,
    borderRadius: 50
  }
}));

function AttachFileToStatusComposer({
  rid,
  previewRef,
  filesUploadRef,
  control: Control
}: any) {
  const { i18n } = useGlobal();
  const inputRef = React.useRef<HTMLInputElement>();

  const [, , checkValidateImage] = useCheckImageSize();

  const onChangeImage = () => {
    if (!inputRef.current.files.length) return;

    const validate = checkValidateImage(inputRef.current.files, true);

    if (!validate) return;

    if (previewRef) {
      previewRef.current?.attachFiles(inputRef.current.files);
    }

    if (filesUploadRef?.current) {
      filesUploadRef.current?.attachFiles(inputRef.current.files);
    }
  };

  const attachImages = () => {
    inputRef.current.click();
  };

  return (
    <div>
      <Tooltip title={i18n.formatMessage({ id: 'send_photo' })} placement="top">
        <WrapperButtonIcon
          data-testid={camelCase('_chat attachment composer')}
          onClick={attachImages}
        >
          <LineIcon icon="ico-photo-o" />
        </WrapperButtonIcon>
      </Tooltip>
      <input
        data-testid="inputAttachPhoto"
        onChange={onChangeImage}
        multiple
        ref={inputRef}
        style={{ display: 'none' }}
        type="file"
        accept="image/*"
      />
    </div>
  );
}

export default React.memo(
  AttachFileToStatusComposer,
  (prev, next) => prev.rid === next.rid
);
