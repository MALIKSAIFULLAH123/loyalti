/**
 * @type: formElement
 * name: form.element.SinglePhotoFile
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form';
import { BasicFileItem, useGlobal } from '@metafox/framework';
import { DropFileBox, LineIcon, Image } from '@metafox/ui';
import {
  getFileExtension,
  parseFileSize,
  shortenFileName,
  isVideoType,
  checkFileAccept
} from '@metafox/utils';
import {
  Button,
  FormControl,
  Tooltip,
  styled,
  Link,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, uniqueId } from 'lodash';
import React, { useRef, useState } from 'react';
import ErrorMessage from '../ErrorMessage';
import CircularProgressUpload from '../CircularProgressUpload';

// apply this style help automation ci works property
const fixInputStyle: React.CSSProperties = {
  width: 2,
  right: 0,
  position: 'absolute',
  opacity: 0,
  display: 'none'
};

const AddPhotoButton = styled(Button, { name: 'AddPhotoButton' })(
  ({ theme }) => ({
    fontWeight: 'bold',
    margin: theme.spacing(0, 2),
    height: 'auto'
  })
);

const Root = styled(FormControl, { name: 'Root' })(({ theme }) => ({
  margin: theme.spacing(2, 0, 1),
  overflow: 'hidden'
}));

const Preview = styled('div', { name: 'Preview' })(({ theme }) => ({
  marginTop: -5,
  borderRadius: 4,
  width: '100%',
  maxWidth: '100%',
  overflow: 'hidden',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative',
  '& button': {
    '& + button': {
      marginLeft: theme.spacing(1)
    }
  }
}));

const RemoveBtn = styled('div', {
  name: 'RemoveBtn',
  slot: 'removeBtn'
})(({ theme }) => ({
  width: theme.spacing(3),
  height: theme.spacing(3),
  borderRadius: theme.spacing(1.5),
  backgroundColor: 'rgba(0,0,0,0.89)',
  color: '#fff',
  position: 'absolute',
  top: theme.spacing(2),
  right: theme.spacing(2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer'
}));

const Controls = styled('div', {
  name: 'Controls',
  slot: 'Controls'
})(({ theme }) => ({
  borderRadius: 4,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'absolute',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%'
}));

const ImageWrapper = styled('div', {
  name: 'ImageWrapper',
  slot: 'ImageWrapper',
  shouldForwardProp: prop =>
    prop !== 'widthPhoto' &&
    prop !== 'haveError' &&
    prop !== 'isMobile' &&
    prop !== 'hoverState'
})<{
  widthPhoto?: number;
  haveError?: boolean;
  hoverState?: boolean;
  isMobile?: boolean;
}>(({ theme, haveError, hoverState, widthPhoto, isMobile }) => ({
  width: isMobile ? '100%' : widthPhoto,
  maxWidth: isMobile ? '100%' : widthPhoto,
  overflow: 'hidden',
  position: 'relative',
  borderStyle: 'solid',
  borderWidth: '1px',
  borderColor:
    theme.palette.mode === 'light' ? '#0000003b' : 'rgba(255, 255, 255, 0.23)',
  borderRadius: 4,
  ...(hoverState && {
    borderColor: theme.palette.mode === 'light' ? '#000' : '#fff'
  }),
  ...(haveError && {
    borderColor: theme.palette.error.main
  })
}));

const LinkChange = styled('div', { name: 'LinkChange' })(({ theme }) => ({
  marginTop: theme.spacing(1),
  marginBottom: theme.spacing(1),
  cursor: 'pointer'
}));

const LabelStyled = styled(Typography, {
  name: 'LabelStyled',
  shouldForwardProp: prop => prop !== 'haveError'
})<{
  haveError?: boolean;
}>(({ theme, haveError }) => ({
  marginBottom: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

const readFile = (file: File) => {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.addEventListener('load', () => resolve(reader.result), false);
    reader.readAsDataURL(file);
  });
};

export default function ItemPhotoField({
  name,
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    required,
    variant = 'outlined',
    item_type,
    max_upload_filesize,
    upload_url,
    preview_url: initialPreviewURL,
    disabled,
    returnBase64 = false,
    thumbnail_sizes,
    aspectRatio = '1:1',
    widthPhoto = '200px',
    accept = 'image/*',
    isDropFile = false
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { dialogBackend, i18n, useIsMobile, ParserPreviewPhoto } = useGlobal();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );
  const placeholder = config.placeholder || 'add_photo';
  const [previewUrl, setPreviewUrl] = useState(initialPreviewURL);
  const [hasPreview, setHasPreview] = useState(Boolean(previewUrl));
  const inputRef = useRef<HTMLInputElement>();
  const [hoverState, setHoverState] = useState<boolean>(false);
  const Ratio = aspectRatio.replace(':', '');
  const isMobile = useIsMobile();
  const [progress, setProgress] = React.useState(returnBase64 ? 100 : 0);

  const handleConvertBase64 = async (fileData: File, cb) => {
    if (fileData) {
      const base64 = await readFile(fileData);
      cb(base64);
    }
  };

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const onUploadProgress = event => {
    const progress = Math.round((event.loaded * 100) / event.total);
    setProgress(progress);
    // eslint-disable-next-line no-console
  };

  const handleInputChange = (_: any, fileInput?: any) => {
    if (!meta?.touched) {
      setTouched(true);
    }

    const file = fileInput || inputRef.current.files;

    if (!file && !file.length) return;

    const type = isVideoType(file[0].type) ? 'video' : 'photo';
    const fileItem: BasicFileItem = {
      id: 0,
      uid: uniqueId('file'),
      source: URL.createObjectURL(file[0]),
      file: file[0],
      file_name: file[0].name,
      file_size: file[0].size,
      file_type: type,
      fileItemType: item_type,
      status: 'update',
      upload_url,
      extension: getFileExtension(file[0].name),
      thumbnail_sizes,
      onUploadProgress
    };

    const fileItemSize = fileItem.file.size;

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

    if (!checkFileAccept(fileItem?.file?.type, accept)) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'file_accept_type_fail' })
      });

      if (inputRef?.current) {
        inputRef.current.value = null;
      }

      return;
    }

    if (fileItem) {
      setPreviewUrl(fileItem.source);
      setHasPreview(true);

      if (returnBase64) {
        handleConvertBase64(fileItem?.file, result => {
          setValue({ base64: result });
        });
      } else {
        setValue(fileItem);
      }
    }
  };

  const handleDeletePhoto = () => {
    setPreviewUrl(null);
    setHasPreview(false);
    setValue(
      initialPreviewURL ? { status: 'remove', temp_file: 0 } : undefined
    );
  };

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const onDnDFile = files => {
    if (!files.length) return;

    const file = Object.assign({ length: files.length }, files);

    if (!file) return;

    handleInputChange(null, file);
  };

  const onParseFile = parseFile => {
    setValue({ ...parseFile.preUploadFile, status: 'update' });
    setPreviewUrl(parseFile?.preUploadFile?.url);
    setHasPreview(true);
  };

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  if (hasPreview) {
    return (
      <>
        <Root
          fullWidth
          variant={variant as any}
          margin="normal"
          data-testid={camelCase(`field ${name}`)}
        >
          <LabelStyled haveError={haveError}>
            {config.label}
            {required ? '(*)' : ''}
          </LabelStyled>
          <ImageWrapper
            haveError={haveError}
            hoverState={hoverState}
            widthPhoto={widthPhoto}
            isMobile={isMobile}
          >
            <ParserPreviewPhoto item={field.value} onParse={onParseFile}>
              <Preview
                onMouseOver={() => setHoverState(true)}
                onMouseLeave={() => setHoverState(false)}
              >
                <Image src={previewUrl} aspectRatio={Ratio} backgroundImage />
                {formik.isSubmitting ? (
                  <CircularProgressUpload
                    uploading={formik.isSubmitting}
                    value={progress}
                  />
                ) : (
                  <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                    <RemoveBtn
                      onClick={handleDeletePhoto}
                      data-testid={camelCase(`button_remove ${name}`)}
                    >
                      <LineIcon icon="ico-close" />
                    </RemoveBtn>
                  </Tooltip>
                )}
              </Preview>
            </ParserPreviewPhoto>
          </ImageWrapper>
          <LinkChange>
            <Link
              onClick={handleControlClick}
              color="primary"
              data-testid={camelCase(`button add ${name}`)}
              disabled={disabled || forceDisabled || formik.isSubmitting}
            >
              {i18n.formatMessage({ id: 'change' })}
            </Link>
          </LinkChange>
        </Root>
        {haveError ? <ErrorMessage error={meta.error} /> : null}
        <input
          onClick={handleResetValue}
          ref={inputRef}
          type="file"
          aria-hidden
          accept={accept}
          style={fixInputStyle}
          data-testid={camelCase(`input ${name}`)}
          onChange={handleInputChange}
        />
      </>
    );
  }

  return (
    <Root
      variant={variant as any}
      margin="normal"
      data-testid={camelCase(`field ${name}`)}
    >
      <LabelStyled haveError={haveError}>
        {config.label}
        {required ? '(*)' : ''}
      </LabelStyled>

      {isDropFile ? (
        <DropFileBox
          onDrop={files => onDnDFile(files)}
          render={({ canDrop, isOver }) => (
            <ImageWrapper
              haveError={haveError}
              hoverState={hoverState}
              widthPhoto={widthPhoto}
              isMobile={isMobile}
            >
              <Image src={previewUrl} aspectRatio={Ratio} backgroundImage />
              <Controls
                role="button"
                onClick={handleControlClick}
                onMouseOver={() => setHoverState(true)}
                onMouseLeave={() => setHoverState(false)}
              >
                <AddPhotoButton
                  size="small"
                  color="primary"
                  variant="outlined"
                  data-testid={camelCase(`button add ${name}`)}
                  disabled={disabled || forceDisabled || formik.isSubmitting}
                  startIcon={<LineIcon icon="ico-photo-plus-o" />}
                >
                  {i18n.formatMessage({ id: placeholder })}
                </AddPhotoButton>
              </Controls>
            </ImageWrapper>
          )}
        />
      ) : (
        <ImageWrapper
          haveError={haveError}
          hoverState={hoverState}
          widthPhoto={widthPhoto}
          isMobile={isMobile}
        >
          <Image src={previewUrl} aspectRatio={Ratio} backgroundImage />
          <Controls
            role="button"
            onClick={handleControlClick}
            onMouseOver={() => setHoverState(true)}
            onMouseLeave={() => setHoverState(false)}
          >
            <AddPhotoButton
              size="small"
              color="primary"
              variant="outlined"
              data-testid={camelCase(`button add ${name}`)}
              disabled={disabled || forceDisabled || formik.isSubmitting}
              startIcon={<LineIcon icon="ico-photo-plus-o" />}
            >
              {i18n.formatMessage({ id: placeholder })}
            </AddPhotoButton>
          </Controls>
        </ImageWrapper>
      )}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
      {config.description ? (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1} mb={1}>
          {config.description}
        </Typography>
      ) : null}
      <input
        onClick={handleResetValue}
        ref={inputRef}
        type="file"
        aria-hidden
        accept={accept}
        data-testid={camelCase(`input ${name}`)}
        onChange={handleInputChange}
        style={fixInputStyle}
      />
    </Root>
  );
}
