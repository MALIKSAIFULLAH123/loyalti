import { BasicFileItem, useGlobal } from '@metafox/framework';
import { handleConvertBase64 } from '@metafox/photo/utils/helper';
import { DropFileBox, LineIcon, Image } from '@metafox/ui';
import {
  getFileExtension,
  parseFileSize,
  shortenFileName,
  isVideoType,
  checkFileAccept,
  getImageSrc
} from '@metafox/utils';
import { Button, FormControl, Tooltip, styled, Link } from '@mui/material';
import { camelCase, uniqueId } from 'lodash';
import React, { useRef, useState } from 'react';

const name = 'UploadThumbnail';

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
  shouldForwardProp: prop => prop !== 'haveError' && prop !== 'hoverState'
})<{
  haveError?: boolean;
  hoverState?: boolean;
  isMobile?: boolean;
}>(({ theme, haveError, hoverState }) => ({
  height: '150px',
  width: '100%',
  maxWidth: '100%',
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

interface Props {
  config?: any;
  value?: any;
  setValue?: any;
}

export default function UploadThumbnail({ config, value, setValue }: Props) {
  const {
    preview_url: initialPreviewURL,
    aspectRatio = '169',
    accept = 'image/*',
    isDropFile = false
  } = config || {};
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { dialogBackend, i18n, useIsMobile, ParserPreviewPhoto, getSetting } =
    useGlobal();
  const maxFileSize: any = getSetting(
    'core.attachment.maximum_file_size_each_attachment_can_be_uploaded'
  );
  const max_upload_filesize = maxFileSize || 0;

  const defaultThumbnailVideo = React.useMemo(() => {
    if (initialPreviewURL?.image)
      return getImageSrc(initialPreviewURL?.image, '500');

    return undefined;
  }, [initialPreviewURL]);

  const [previewUrl, setPreviewUrl] = useState(defaultThumbnailVideo);
  const [hasPreview, setHasPreview] = useState(Boolean(previewUrl));
  const inputRef = useRef<HTMLInputElement>();
  const [hoverState, setHoverState] = useState<boolean>(false);
  const Ratio = aspectRatio.replace(':', '');
  const isMobile = useIsMobile();

  React.useEffect(() => {
    if (!initialPreviewURL?.file) return;

    handleConvertBase64(initialPreviewURL?.file, result => {
      setPreviewUrl(result);
      setHasPreview(true);
    });
  }, [initialPreviewURL?.file]);

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const handleInputChange = (_: any, fileInput?: any) => {
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
      fileItemType: 'photo',
      status: 'new',
      upload_url: '/file',
      extension: getFileExtension(file[0].name),
      thumbnail_sizes: ['150', '240', '500']
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
      setValue(fileItem);
    }
  };

  const handleDeletePhoto = () => {
    setPreviewUrl(null);
    setHasPreview(false);
    setValue(value.status === 'new' ? undefined : { status: 'remove' });
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
    setValue({ ...parseFile.preUploadFile, status: 'new' });
    setPreviewUrl(parseFile?.preUploadFile?.url);
    setHasPreview(true);
  };

  if (hasPreview) {
    return (
      <>
        <Root
          fullWidth
          variant={'outlined'}
          margin="normal"
          data-testid={camelCase(`field ${name}`)}
        >
          <ImageWrapper hoverState={hoverState} isMobile={isMobile}>
            <ParserPreviewPhoto item={value} onParse={onParseFile}>
              <Preview
                onMouseOver={() => setHoverState(true)}
                onMouseLeave={() => setHoverState(false)}
              >
                <Image src={previewUrl} aspectRatio={Ratio} backgroundImage />
                <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                  <RemoveBtn
                    onClick={handleDeletePhoto}
                    data-testid={camelCase(`button_remove ${name}`)}
                  >
                    <LineIcon icon="ico-close" />
                  </RemoveBtn>
                </Tooltip>
              </Preview>
            </ParserPreviewPhoto>
          </ImageWrapper>
          <LinkChange>
            <Link
              onClick={handleControlClick}
              color="primary"
              data-testid={camelCase(`button add ${name}`)}
            >
              {i18n.formatMessage({ id: 'change' })}
            </Link>
          </LinkChange>
        </Root>
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
      variant={'outlined'}
      margin="normal"
      data-testid={camelCase(`field ${name}`)}
    >
      {isDropFile ? (
        <DropFileBox
          onDrop={files => onDnDFile(files)}
          render={({ canDrop, isOver }) => (
            <ImageWrapper hoverState={hoverState} isMobile={isMobile}>
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
                  startIcon={<LineIcon icon="ico-photo-plus-o" />}
                >
                  {i18n.formatMessage({ id: 'choose_a_thumbnail' })}
                </AddPhotoButton>
              </Controls>
            </ImageWrapper>
          )}
        />
      ) : (
        <ImageWrapper hoverState={hoverState} isMobile={isMobile}>
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
              startIcon={<LineIcon icon="ico-photo-plus-o" />}
            >
              {i18n.formatMessage({ id: 'choose_a_thumbnail' })}
            </AddPhotoButton>
          </Controls>
        </ImageWrapper>
      )}
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
