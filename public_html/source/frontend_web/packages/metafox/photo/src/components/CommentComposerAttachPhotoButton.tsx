/**
 * @type: ui
 * name: commentComposer.control.attachPhoto
 * chunkName: comment
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import { shortenFileName, parseFileSize } from '@metafox/utils';
interface CommentComposerPluginControlProps {
  testid: string;
  icon: string;
  title: string;
  label?: string;
  onClick: () => void;
  disabled?: boolean;
}
interface CommentComposerPluginProps {
  disabled?: boolean;
  control: React.FC<CommentComposerPluginControlProps>;
  previewRef: any;
  onStickerClick: any;
  editorRef: any;
  onAttachFiles: any;
}

export default function AttachPhotoButton({
  onAttachFiles,
  previewRef,
  control: Control
}: CommentComposerPluginProps) {
  const { i18n, useLimitFileSize, dialogBackend } = useGlobal();
  const inputRef = React.useRef<HTMLInputElement>();
  const { photo: photoMaxSize } = useLimitFileSize();

  const onClick = () => {
    inputRef.current.click();
  };

  const onChange = () => {
    if (!inputRef.current.files.length) return;

    const files = inputRef.current.files;
    const file = files[0];
    const fileItemSize = file.size;

    if (fileItemSize > photoMaxSize && photoMaxSize !== 0) {
      dialogBackend.alert({
        message: i18n.formatMessage(
          { id: 'warning_upload_limit_one_file' },
          {
            fileName: shortenFileName(file.name, 30),
            fileSize: parseFileSize(file.size),
            maxSize: parseFileSize(photoMaxSize)
          }
        )
      });
      inputRef.current.value = null;

      return;
    }

    if (previewRef) {
      previewRef.current?.attachFiles(inputRef.current.files);
    }

    if (onAttachFiles) onAttachFiles(inputRef.current.files);
  };

  return (
    <>
      <Control
        title={i18n.formatMessage({ id: 'attach_a_photo' })}
        onClick={onClick}
        testid="buttonAttachPhoto"
        icon="ico-photo-o"
      />
      <input
        data-testid="inputAttachPhoto"
        onChange={onChange}
        multiple={false}
        ref={inputRef}
        className="srOnly"
        type="file"
        accept="image/*"
      />
    </>
  );
}
