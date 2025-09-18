import { LineIcon, TruncateText } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'PreviewUploadFile';

const PreviewImage = styled('img', { name, slot: 'previewImage' })(
  ({ theme }) => ({
    borderRadius: theme.spacing(1),
    maxWidth: '52px',
    width: '52px',
    height: '52px',
    marginRight: theme.spacing(2),
    objectFit: 'cover'
  })
);

const StyledWapperFile = styled('div', {
  name,
  slot: 'PreviewFile',
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  borderRadius: theme.spacing(1),
  maxWidth: '150px',
  width: '150px',
  height: '52px',
  marginRight: theme.spacing(2),
  fontSize: theme.spacing(1.5),
  display: 'flex',
  alignItems: 'center',
  background:
    theme.palette.mode === 'light'
      ? theme.palette.background.default
      : theme.palette.grey['500'],
  ...(isAllPage && {
    maxWidth: '180px',
    width: '180px',
    overflowX: 'hidden'
  })
}));

const StyledIconFile = styled('div', { name, slot: 'IconFile' })(
  ({ theme }) => ({
    borderRadius: '100%',
    maxWidth: '28px',
    width: '28px',
    height: '28px',
    margin: theme.spacing(0, 1),
    padding: theme.spacing(0, 1),
    color: theme.palette.default.contrastText,
    background:
      theme.palette.mode === 'light'
        ? theme.palette.grey['500']
        : theme.palette.grey['700'],
    display: 'inline-flex',
    justifyContent: 'center',
    alignItems: 'center'
  })
);

const CloseButton = styled('div', { name, slot: 'closeButton' })(
  ({ theme }) => ({
    position: 'absolute',
    top: '-8px',
    right: '8px',
    cursor: 'pointer',
    width: theme.spacing(2.5),
    height: theme.spacing(2.5),
    backgroundColor: theme.palette.grey['500'],
    borderRadius: '50%',
    fontSize: theme.mixins.pxToRem(8),
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

const StyledFileName = styled(TruncateText)(({ theme }) => ({
  paddingRight: theme.spacing(1),
  wordBreak: 'break-word',
  p: {
    fontSize: theme.mixins.pxToRem(13)
  }
}));

interface Props {
  onRemove: () => void;
  file: any;
  isAllPage?: boolean;
}

function FilePreviewItem({ onRemove, file, isAllPage }: Props) {
  const fileType = file['type'];
  const validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
  const isImage = validImageTypes.includes(fileType);
  const objectUrl = isImage ? URL.createObjectURL(file) : '';

  return (
    <Box display="inline-flex" position="relative">
      {isImage ? (
        <PreviewImage src={objectUrl} alt="Preview" />
      ) : (
        <StyledWapperFile isAllPage={isAllPage}>
          <StyledIconFile>
            <LineIcon icon="ico-paperclip-alt" />
          </StyledIconFile>
          <StyledFileName lines={2}>{file.name}</StyledFileName>
        </StyledWapperFile>
      )}
      <Box position="absolute" top={1} right={1}>
        <CloseButton
          data-testid="buttonRemove"
          onClick={onRemove}
          aria-label="Close"
        >
          <LineIcon icon="ico-close" />
        </CloseButton>
      </Box>
    </Box>
  );
}

export default FilePreviewItem;
