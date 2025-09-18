import useCheckImageSize from '@metafox/chat/hooks/useCheckImageSize';
import { PreviewUploadFileHandle } from '@metafox/chat/types';
import { RefOf } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import FilePreviewItem from './FilePreviewItem';
import { useChatRoom } from '@metafox/chat/hooks';
import { isEmpty } from 'lodash';

const Root = styled('div', {
  shouldForwardProp: props => props !== 'isAllPage'
})<{ isAllPage?: boolean }>(({ theme, isAllPage }) => ({
  background: theme.palette.background.paper,
  padding: theme.spacing(2, 1, 0, 1),
  display: 'flex',
  overflow: 'hidden',
  borderTop: theme.mixins.border('secondary'),
  userSelect: 'none',
  height: '85px',
  minHeight: '85px',
  ...(isAllPage && {
    height: '90px',
    minHeight: '90px'
  })
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
  rid?: string;
}

function PreviewUploadFile(
  { filesUploadRef, isAllPage, rid }: Props,
  ref: RefOf<PreviewUploadFileHandle>
) {
  const chatRoom = useChatRoom(rid);

  const scrollRef = React.useRef<HTMLDivElement>();
  const inputRef = React.useRef<HTMLInputElement>();

  const [validFileItems, setValidFileItems, handleProcessFiles] =
    useCheckImageSize({
      inputRef
    });

  const removeItem = index => {
    const filesList = [...validFileItems];

    if (index > -1) {
      filesList.splice(index, 1);
      setValidFileItems([...filesList]);

      if (filesUploadRef) {
        filesUploadRef.current?.removeFile(index);
      }
    }
  };

  React.useImperativeHandle(ref, () => {
    return {
      attachFiles: (files: File[]) => {
        if (files?.length) {
          handleProcessFiles(files);
        }
      },
      clear: () => {
        setValidFileItems([]);
      }
    };
  });

  const attachImages = () => {
    inputRef.current.click();
  };

  const addFile = () => {
    attachImages();
  };

  const onChangeImage = () => {
    if (!inputRef.current?.files.length) return;

    const validate = handleProcessFiles(inputRef.current.files);

    if (!validate) return;
  };

  React.useEffect(() => {
    if (filesUploadRef) {
      filesUploadRef.current?.attachFiles(validFileItems);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [validFileItems.length]);

  if (
    !isEmpty(chatRoom?.roomProgress) ||
    !validFileItems ||
    !validFileItems?.length
  )
    return null;

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
          {Object.values(validFileItems).map((item, index) => (
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
    </Root>
  );
}

export default React.forwardRef<PreviewUploadFileHandle, Props>(
  PreviewUploadFile
);
