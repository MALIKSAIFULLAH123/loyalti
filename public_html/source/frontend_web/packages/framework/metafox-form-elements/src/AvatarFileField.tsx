/**
 * @type: formElement
 * name: form.element.AvatarUpload
 * chunkName: formExtras
 */

import { useGlobal } from '@metafox/framework';
import { FormFieldProps } from '@metafox/form';
import { LineIcon, Image } from '@metafox/ui';
import { shortenFileName, parseFileSize } from '@metafox/utils';
import { FormControl, styled, Box, IconButton } from '@mui/material';
import { useField } from 'formik';
import { camelCase, uniqueId } from 'lodash';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import ErrorMessage from './ErrorMessage';

const readFile = (file: File) => {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.addEventListener('load', () => resolve(reader.result), false);
    reader.readAsDataURL(file);
  });
};

// apply this style help automation ci works property
const fixInputStyle: React.CSSProperties = {
  width: 2,
  position: 'absolute',
  right: 0,
  opacity: 0
};

export interface AttachmentItemProps {
  file_name: string;
  is_video?: boolean;
  is_image?: boolean;
  download_url: string;
  id?: number;
  file: File;
  key: string;
  fileItemType: string;
  classes?: Record<'item' | 'itemInfo', string>;
  handleDelete?: (id: number) => void;
  index?: number;
}

const AvatarWrapper = styled(Box, { name: 'AvatarWrapper' })(({ theme }) => ({
  borderRadius: '100%',
  position: 'relative',
  background:
    theme.palette.mode === 'dark'
      ? theme.palette.grey[600]
      : theme.palette.grey[100]
}));

const ButtonIcon = styled(IconButton, { name: 'AvatarButtonIcon' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%',
    fontSize: '40px',
    position: 'absolute',
    left: 0,
    top: 0,
    zIndex: 3
  })
);

const RemoveButton = styled(IconButton, { name: 'AvatarRemoveButton' })(
  ({ theme }) => ({
    width: '100%',
    height: '100%',
    fontSize: '40px',
    position: 'absolute',
    left: 0,
    top: 0,
    zIndex: 3,
    opacity: 0,
    '&:hover': {
      opacity: 1
    }
  })
);

export default function AttachmentField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const { dialogBackend, i18n } = useGlobal();
  const [field, meta, { setValue }] = useField(name ?? 'avatarUpload');

  const { item_type, max_upload_filesize, disabled, accept, margin } = config;

  const [file, setFile] = useState<AttachmentItemProps & { url?: string }>(
    field.value
  );

  const inputRef = useRef<HTMLInputElement>();

  useEffect(() => {
    if (file?.file) {
      handleConvertBase64(file?.file, result => {
        setValue({ base64: result });
      });
    } else if (file?.url) {
      setValue({});
    } else {
      setValue({ is_delete: true });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [file]);

  const validFileAccept = fileType => {
    return accept
      ? accept
          .replace(/\s/g, '')
          .split(',')
          .filter(x => {
            return new RegExp(x.replace('*', '.*')).test(fileType);
          }).length > 0
      : true;
  };

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const handleDelete = useCallback(
    () => {
      setFile(null);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleConvertBase64 = async (fileData: File, cb) => {
    if (fileData) {
      const base64 = await readFile(fileData);

      cb(base64);
    }
  };

  const handleInputChange = useCallback(() => {
    const file = inputRef.current.files;

    if (file.length === 0) return;

    if (!validFileAccept(file.item(0).type)) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'picture_format_is_not_correct' })
      });
      inputRef.current.value = null;

      return;
    }

    const fileItem: AttachmentItemProps = {
      file_name: file.item(0).name,
      download_url: URL.createObjectURL(file.item(0)),
      file: file.item(0),
      key: uniqueId('file'),
      fileItemType: item_type
    };

    const fileItemSize = fileItem.file.size;
    const fileItemName = fileItem.file_name;

    if (fileItemSize > max_upload_filesize && max_upload_filesize !== 0) {
      dialogBackend.alert({
        message: i18n.formatMessage(
          { id: 'warning_upload_limit_one_file' },
          {
            fileName: shortenFileName(fileItemName, 30),
            fileSize: parseFileSize(fileItem.file.size),
            maxSize: parseFileSize(max_upload_filesize)
          }
        )
      });

      return;
    }

    if (fileItem) {
      setFile(fileItem);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [file]);

  const handleControlClick = useCallback(() => {
    inputRef.current.click();
  }, []);

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  return (
    <FormControl margin={margin} data-testid={camelCase(`field ${name}`)}>
      <AvatarWrapper sx={{ width: '160px', height: '160px' }}>
        {file?.download_url || file?.url ? (
          <>
            <Image
              shape={'circle'}
              src={file?.download_url || file?.url}
              aspectRatio={'11'}
            />
            <RemoveButton
              onClick={() => handleDelete()}
              disabled={disabled || forceDisabled || formik.isSubmitting}
              sx={{
                color: 'white',
                background: 'rgba(0,0,0,0.3)'
              }}
            >
              <LineIcon icon="ico-trash" />
            </RemoveButton>
          </>
        ) : (
          <ButtonIcon
            data-testid={camelCase(`button ${name}`)}
            onClick={handleControlClick}
            disabled={disabled || forceDisabled || formik.isSubmitting}
          >
            <LineIcon icon="ico-camera" />
          </ButtonIcon>
        )}
      </AvatarWrapper>
      {haveError && <ErrorMessage error={meta.error} />}
      <input
        onClick={handleResetValue}
        data-testid={camelCase(`input ${name}`)}
        ref={inputRef}
        style={fixInputStyle}
        accept={accept}
        aria-hidden
        type="file"
        multiple={false}
        onChange={handleInputChange}
        hidden
      />
    </FormControl>
  );
}
