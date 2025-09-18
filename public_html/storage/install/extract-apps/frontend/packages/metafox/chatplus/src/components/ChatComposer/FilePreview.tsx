import { PreviewUploadFileHandle } from '@metafox/chatplus/types';
import { RefOf } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import FilePreviewItem from './FilePreviewItem';

const Root = styled('div', {
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  background: theme.palette.background.paper,
  padding: theme.spacing(2, 1, 0, 1),
  display: 'flex',
  overflow: 'hidden',
  borderTop: theme.mixins.border('secondary'),
  userSelect: 'none',
  height: '90px',
  minHeight: '90px'
}));

const WrapperFile = styled('div')(({ theme }) => ({
  display: 'flex',
  paddingTop: theme.spacing(1),
  marginBottom: theme.spacing(0.5),
  paddingBottom: theme.spacing(0.5)
}));

const StyledAddFile = styled('div', { slot: 'ButtonAdd' })(({ theme }) => ({
  marginRight: theme.spacing(1),
  cursor: 'pointer',
  span: {
    background:
      theme.palette.mode === 'light'
        ? theme.palette.background.default
        : theme.palette.grey['500'],
    '&:hover': {
      background: theme.palette.action.hover
    },
    borderRadius: theme.spacing(1),
    maxWidth: '52px',
    width: '52px',
    height: '52px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: theme.spacing(2.5)
  }
}));

interface Props {
  files?: File[];
  onChange?: (temp_file: number) => void;
  filesUploadRef?: any;
  isAllPage?: boolean;
}

function PreviewUploadFile(
  { filesUploadRef, isAllPage }: Props,
  ref: RefOf<PreviewUploadFileHandle>
) {
  const scrollRef = React.useRef<HTMLDivElement>();
  const [listFiles, setListFiles] = React.useState<File[]>([]);

  const [typeUpload, setTypeUpload] = React.useState('image');

  const inputRef = React.useRef<HTMLInputElement>();
  const fileUploadRef = React.useRef<HTMLInputElement>();

  const removeItem = index => {
    const filesList = [...listFiles];

    if (index > -1) {
      filesList.splice(index, 1);
      setListFiles([...filesList]);

      if (filesUploadRef) {
        filesUploadRef.current?.removeFile(index);
      }
    }
  };

  React.useImperativeHandle(ref, () => {
    return {
      attachFiles: (files: File[]) => {
        if (files?.length) {
          setListFiles(files);
        }
      },
      typeUpload: type => {
        if (type) {
          setTypeUpload(type);
        }
      },
      clear: () => {
        setListFiles([]);
      },
      checkIsLoading: () => {},
      listFiles: () => listFiles
    };
  });

  if (!listFiles || !listFiles?.length) return null;

  const attachImages = () => {
    inputRef.current.click();
  };

  const attachFile = () => {
    fileUploadRef.current.click();
  };

  const addFile = () => {
    if (typeUpload === 'image') {
      attachImages();

      return;
    }

    attachFile();
  };

  const onChangeImage = () => {
    if (!inputRef.current.files.length) return;

    const data = [...listFiles, ...inputRef.current.files];
    setListFiles(data);

    if (filesUploadRef) {
      filesUploadRef.current?.attachFiles(data);
    }
  };

  const fileUploadChanged = () => {
    if (!fileUploadRef.current.files.length) return;

    const data = [...listFiles, ...fileUploadRef.current.files];
    setListFiles(data);

    if (filesUploadRef) {
      filesUploadRef.current?.attachFiles(data);
    }
  };

  return (
    <Root isAllPage={isAllPage}>
      <ScrollContainer
        autoHide={false}
        autoHeight={false}
        ref={scrollRef}
        style={{ height: 'auto' }}
      >
        <WrapperFile>
          <StyledAddFile onClick={addFile}>
            <span>
              <LineIcon icon="ico-text-file-plus" />
            </span>
          </StyledAddFile>
          {Object.values(listFiles).map((item, index) => (
            <FilePreviewItem
              key={index}
              file={item}
              onRemove={() => removeItem(index)}
              isAllPage={isAllPage}
            />
          ))}
        </WrapperFile>
      </ScrollContainer>
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
    </Root>
  );
}

export default React.forwardRef<PreviewUploadFileHandle, Props>(
  PreviewUploadFile
);
