/**
 * @type: formElement
 * name: form.element.SingleVideoFile
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import { BasicFileItem, useGlobal } from '@metafox/framework';
import { DropFileBox, LineIcon } from '@metafox/ui';
import {
  shortenFileName,
  parseFileSize,
  checkFileAccept
} from '@metafox/utils';
import {
  Box,
  Button,
  FormControl,
  Typography,
  LinearProgress,
  Link as LinkButton
} from '@mui/material';
import { styled } from '@mui/material/styles';
import { useField } from 'formik';
import { camelCase, uniqueId } from 'lodash';
import React, { useRef } from 'react';
import ErrorMessage from '../ErrorMessage';

export interface DropButtonProps {
  isOver?: boolean;
}

const name = 'SingleVideoField';

const DropButton = styled(Button, {
  name,
  slot: 'DropButton'
})<DropButtonProps>(({ theme, isOver }) => ({
  ...(isOver && {
    backgroundColor: theme.palette.action.hover
  })
}));

const VideoUploaded = styled('div', {
  name,
  slot: 'VideoUploaded'
})<{}>(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  '& .ico-video': {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

const DropzoneBox = styled('div', {
  name,
  slot: 'DropzoneBox'
})<{}>(({ theme }) => ({
  width: 'fit-content'
}));

export default function SingleVideoField({
  name,
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    label = 'Select Video',
    description,
    item_type,
    accept = 'video/*',
    max_upload_filesize,
    upload_url,
    disabled,
    storage_id
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { dialogBackend, i18n } = useGlobal();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );
  const [showVideoName, setShowVideoName] = React.useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>();

  const [progress, setProgress] = React.useState(0);

  const onUploadProgress = event => {
    const progress = Math.round((event.loaded * 100) / event.total);

    setProgress(progress);
    // eslint-disable-next-line no-console
  };

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const handleRemoveVideo = () => {
    setShowVideoName(false);
    setValue(undefined);
  };

  const handleFile = file => {
    const fileItem: BasicFileItem = {
      id: 0,
      uid: uniqueId('file'),
      source: URL.createObjectURL(file),
      file,
      file_name: file.name,
      file_type: file.type,
      file_size: file.size,
      fileItemType: item_type,
      upload_url,
      storage_id: storage_id ?? null,
      status: 'new',
      onUploadProgress
    };

    const fileItemSize = fileItem.file.size;

    if (!checkFileAccept(fileItem?.file?.type, accept)) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'file_accept_type_fail' })
      });

      if (inputRef?.current) {
        inputRef.current.value = null;
      }

      return;
    }

    if (fileItemSize > max_upload_filesize && max_upload_filesize !== 0) {
      dialogBackend.alert({
        message: i18n.formatMessage(
          { id: 'warning_upload_limit_one_file' },
          {
            fileName: shortenFileName(fileItem.file_name, 30),
            fileSize: parseFileSize(fileItem.file.size),
            maxSize: parseFileSize(max_upload_filesize)
          }
        )
      });

      return;
    }

    if (fileItem) {
      setValue(fileItem);
      setShowVideoName(true);
    }
  };

  const handleInputChange = () => {
    if (!meta?.touched) {
      setTouched(true);
    }

    const file = inputRef.current.files;

    if (!file) return;

    handleFile(file.item(0));
  };

  const onDnDFile = files => {
    if (!files) return;

    const file = files[0];

    handleFile(file);
  };

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const haveErr = !!(meta.error && (meta.touched || formik.submitCount));

  return (
    <FormControl
      fullWidth
      margin="normal"
      data-testid={camelCase(`field ${name}`)}
    >
      <Typography variant="body2" color="text.hint" mb={1.5} component="p">
        {showVideoName
          ? i18n.formatMessage({ id: 'video_file_selected' })
          : description}
      </Typography>
      {showVideoName ? (
        <VideoUploaded>
          <LineIcon icon="ico-video" />
          <Box ml={1} mr={1}>
            {shortenFileName(field.value?.file?.name, 30)}
          </Box>
          {formik.isSubmitting ? (
            <Box sx={{ marginLeft: 'auto', minWidth: '100px', pl: 1, mt: 1 }}>
              <LinearProgress variant="determinate" value={progress} />
            </Box>
          ) : (
            <LinkButton
              variant="body2"
              color="primary"
              component="span"
              role="button"
              onClick={handleRemoveVideo}
            >
              {i18n.formatMessage({ id: 'remove' })}
            </LinkButton>
          )}
        </VideoUploaded>
      ) : (
        <DropzoneBox>
          <DropFileBox
            onDrop={files => onDnDFile(files)}
            render={({ canDrop, isOver }) => (
              <DropButton
                onClick={handleControlClick}
                size="small"
                color="primary"
                sx={{ fontWeight: 'bold' }}
                isOver={isOver}
                variant="outlined"
                disabled={disabled || forceDisabled || formik.isSubmitting}
                data-testid={camelCase(`button ${name}`)}
                startIcon={<LineIcon icon="ico-upload" />}
              >
                {label}
              </DropButton>
            )}
          />
        </DropzoneBox>
      )}
      {haveErr ? <ErrorMessage error={meta.error} /> : null}
      <input
        ref={inputRef}
        onClick={handleResetValue}
        className="srOnly"
        type="file"
        data-testid={camelCase(`input ${name}`)}
        multiple={false}
        accept={accept}
        onChange={handleInputChange}
      />
    </FormControl>
  );
}
