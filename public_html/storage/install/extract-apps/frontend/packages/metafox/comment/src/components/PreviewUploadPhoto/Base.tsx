import { PreviewUploadPhotoHandle } from '@metafox/comment/types';
import { RefOf, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, LinearProgress, styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'PreviewUploadPhoto';

const PreviewImage = styled('img', { name, slot: 'previewImage' })(
  ({ theme }) => ({
    maxHeight: 90,
    borderRadius: theme.shape.borderRadius
  })
);
const IndicatorContainer = styled('div', { name, slot: 'indicatorContainer' })(
  ({ theme }) => ({
    position: 'absolute',
    left: theme.spacing(1),
    right: theme.spacing(1),
    bottom: theme.spacing(1)
  })
);
const CloseButton = styled('div', { name, slot: 'closeButton' })(
  ({ theme }) => ({
    cursor: 'pointer',
    width: theme.spacing(3),
    height: theme.spacing(3),
    backgroundColor: theme.palette.background.default,
    borderRadius: '50%',
    fontSize: 'small',
    justifyContent: 'center',
    alignItems: 'center',
    display: 'inline-flex',
    opacity: 0.8,
    color:
      theme.palette.mode === 'light'
        ? theme.palette.text.primary
        : theme.palette.text.primary,
    '&:hover': {
      backgroundColor: theme.palette.background.default,
      color: theme.palette.text.primary,
      border: theme.mixins.border('secondary')
    }
  })
);

interface Props {
  file?: File;
  onChange?: (temp_file: number) => void;
}

interface State {
  temp_file?: number;
  uploading: boolean;
  uploaded: boolean;
  error?: string;
}

function PreviewUploadPhoto(
  { file, onChange }: Props,
  ref: RefOf<PreviewUploadPhotoHandle>
) {
  const { i18n, dispatch } = useGlobal();
  const [selectedFile, setSelectedFile] = React.useState<File>(file);
  const [previewSrc, setPreviewSrc] = React.useState<any>(null);

  const [uploadState, setUploadState] = React.useState<State>({
    uploading: false,
    uploaded: false
  });

  const form = React.useMemo(() => {
    return {
      onSuccess: (temp_file?: number) =>
        setUploadState({ uploading: false, uploaded: true, temp_file }),
      onFailure: (error: string) =>
        setUploadState({ uploading: false, uploaded: true, error })
    };
  }, []);

  const removeItem = React.useCallback(() => {
    setPreviewSrc(undefined);
    setUploadState({ temp_file: 0, uploaded: false, uploading: false });
  }, []);

  React.useEffect(() => {
    if (onChange && typeof uploadState.temp_file !== 'undefined') {
      onChange(uploadState.temp_file);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [uploadState.temp_file]);

  const uploadFiles = React.useCallback(
    (file: File) => {
      setUploadState({ uploading: true, uploaded: false });
      dispatch({
        type: 'file/UPLOAD',
        payload: {
          file,
          params: {
            name: 'file',
            item_type: 'comment_comment'
          }
        },
        meta: { form }
      });
    },
    [dispatch, form]
  );

  React.useEffect(() => {
    if (!selectedFile) return;

    const objectUrl = URL.createObjectURL(selectedFile);

    setPreviewSrc(objectUrl);
    uploadFiles(selectedFile);

    return () => URL.revokeObjectURL(objectUrl);
  }, [selectedFile, uploadFiles]);

  React.useImperativeHandle(ref, () => {
    return {
      attachFiles: (files: FileList) => {
        if (files?.length) {
          setSelectedFile(files[0]);
        }
      },
      clear: () => {
        setUploadState({ uploading: false, uploaded: false, error: undefined });
        setPreviewSrc(undefined);
      },
      checkIsLoading: () => uploadState?.uploading
    };
  });

  if (!previewSrc || uploadState?.error) return null;

  return (
    <Box mt={1} data-testid="blockPreviewPhoto" aria-valuenow={100}>
      {uploadState.uploaded ? (
        <div className="srOnly" data-testid="previewAttachPhoto" />
      ) : null}
      <Box display="inline-flex" position="relative">
        <PreviewImage src={previewSrc} alt="Preview" />
        {uploadState.uploading ? (
          <IndicatorContainer data-testid="previewUploadingPhoto">
            <LinearProgress color="primary" />
          </IndicatorContainer>
        ) : null}
        <Box position="absolute" top={1} right={1}>
          <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
            <CloseButton
              data-testid="buttonRemove"
              onClick={removeItem}
              aria-label="Close"
            >
              <LineIcon icon="ico-close" />
            </CloseButton>
          </Tooltip>
        </Box>
      </Box>
    </Box>
  );
}

export default React.forwardRef<PreviewUploadPhotoHandle, Props>(
  PreviewUploadPhoto
);
