/**
 * @type: formElement
 * name: form.element.SingleAudioField
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import { BasicFileItem, useGlobal } from '@metafox/framework';
import { DropFileBox, InputNotched, LineIcon } from '@metafox/ui';
import {
  shortenFileName,
  parseFileSize,
  checkFileAccept
} from '@metafox/utils';
import {
  Box,
  Button,
  FormControl,
  InputLabel,
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

const name = 'SingleAudioField';

const DropButton = styled(Button, {
  name,
  slot: 'DropButton'
})<DropButtonProps>(({ theme, isOver }) => ({
  ...(isOver && {
    backgroundColor: theme.palette.action.hover
  })
}));

const AudioUploaded = styled('div', {
  name,
  slot: 'AudioUploaded'
})<{}>(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  fontSize: theme.mixins.pxToRem(13),
  '& .ico-music-note-o': {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

const WrapTitleStyled = styled('div', {
  name,
  slot: 'WrapTitleStyled'
})<{}>(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const DropzoneBox = styled('div', {
  name,
  slot: 'DropzoneBox'
})<{}>(({ theme }) => ({
  width: 'fit-content'
}));

const Label = styled(InputLabel, {
  name,
  slot: 'Label'
})<{ haveError?: boolean }>(({ theme, haveError }) => ({
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

const TitleSong = styled('div', {
  name,
  slot: 'TitleSong'
})(({ theme }) => ({
  marginLeft: theme.spacing(1),
  marginRight: theme.spacing(1),
  overflowWrap: 'anywhere'
}));

const titleFile = (name: string) => {
  const lastIndex = name.lastIndexOf('.');

  return name.slice(0, lastIndex);
};

export default function SingleAudioField({
  name,
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    label = 'Select Audio',
    description,
    item_type,
    accept = 'audio/*',
    max_upload_filesize,
    max_length_name,
    upload_url,
    disabled,
    storage_id,
    placeholder,
    autoFillValueName,
    messageAcceptFail
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { dialogBackend, i18n } = useGlobal();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );
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

  const handleRemoveAudio = () => {
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
      status: 'new',
      storage_id: storage_id ?? null,
      onUploadProgress
    };

    const fileItemSize = fileItem.file_size;

    if (
      fileItemSize > max_upload_filesize?.music &&
      max_upload_filesize?.music
    ) {
      dialogBackend.alert({
        message: i18n.formatMessage(
          { id: 'warning_upload_limit_one_file' },
          {
            fileName: shortenFileName(fileItem.file_name, 30),
            fileSize: parseFileSize(fileItem.file_size),
            maxSize: parseFileSize(max_upload_filesize?.music)
          }
        )
      });

      return;
    }

    if (!checkFileAccept(fileItem?.file?.type, accept)) {
      dialogBackend.alert({
        message: i18n.formatMessage({
          id: messageAcceptFail || 'file_accept_type_fail'
        })
      });

      if (inputRef?.current) {
        inputRef.current.value = null;
      }

      return;
    }

    if (fileItem) {
      if (autoFillValueName) {
        formik.setFieldValue(
          autoFillValueName,
          titleFile(fileItem.file_name).slice(0, max_length_name)
        );
      }

      setValue(fileItem);
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
      <Label haveError={haveErr} required variant="outlined" shrink="true">
        {label}
      </Label>
      <Box sx={{ p: 2, pt: 2.5 }}>
        <Typography variant="body2" color="text.hint" mb={0.5} component="p">
          {field.value
            ? i18n.formatMessage({ id: 'audio_file_selected' })
            : description}
        </Typography>
        {field.value ? (
          <AudioUploaded data-testid={camelCase('audio uploaded')}>
            <WrapTitleStyled
              data-testid={camelCase('audio uploaded wrapTitle')}
            >
              <LineIcon icon="ico-music-note-o" />
              <TitleSong data-testid={camelCase('audio uploaded title')}>
                {shortenFileName(
                  field.value?.file_name || field.value?.name,
                  30
                )}
              </TitleSong>
            </WrapTitleStyled>
            {formik.isSubmitting ? (
              <Box sx={{ marginLeft: 'auto', minWidth: '100px', pl: 1, mt: 1 }}>
                <LinearProgress
                  data-testid={camelCase('audio uploaded progress')}
                  variant="determinate"
                  value={progress}
                />
              </Box>
            ) : (
              <LinkButton
                data-testid={camelCase('audio uploaded remove')}
                variant="body2"
                color="primary"
                component="span"
                role="button"
                onClick={handleRemoveAudio}
              >
                {i18n.formatMessage({ id: 'remove' })}
              </LinkButton>
            )}
          </AudioUploaded>
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
                  {placeholder}
                </DropButton>
              )}
            />
          </DropzoneBox>
        )}
        {haveErr ? <ErrorMessage error={meta.error} /> : null}
      </Box>
      <InputNotched
        haveError={haveErr}
        children={config.label}
        variant="outlined"
      />
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
