import { useGlobal, BasicFileItem } from '@metafox/framework';
import React from 'react';
import { isEmpty, uniqueId } from 'lodash';
import {
  shortenFileName,
  isVideoType,
  parseFileSize,
  checkFileAccept,
  getFileType
} from '@metafox/utils';
type Props = {
  initialValues?: BasicFileItem[];
  upload_url?: string;
  maxSizeLimit: Record<string, any>;
  isAcceptVideo?: boolean;
  messageAcceptFail?: string;
  accept?: any;
  inputRef?: React.MutableRefObject<HTMLInputElement>;
  storage_id?: string;
};

export default function useCheckFileSize({
  initialValues,
  upload_url = '',
  maxSizeLimit,
  isAcceptVideo = true,
  messageAcceptFail,
  accept,
  inputRef,
  storage_id
}: Props) {
  const { dialogBackend, i18n } = useGlobal();
  const [validFileItems, setValidFileItems] = React.useState<BasicFileItem[]>(
    initialValues || []
  );
  const maxSizePhoto = maxSizeLimit?.photo ?? maxSizeLimit?.other ?? 0;
  const maxSizeVideo = maxSizeLimit?.video ?? maxSizeLimit?.other ?? 0;

  const handleFiles = (files, currentFiles) => {
    const newItems = [];

    if (isEmpty(files)) return;

    const fileLimitItems = [];

    for (let index = 0; index < files.length; ++index) {
      const file = files[index];
      const fileSize = file.size;
      const maxSize = isVideoType(file?.type) ? maxSizeVideo : maxSizePhoto;
      const fileType = getFileType(file);
      const fileItem: BasicFileItem = {
        id: 0,
        uid: uniqueId(),
        source: URL.createObjectURL(file),
        file_name: file.name,
        file_size: file.size,
        file_type: file.type,
        file,
        upload_url,
        storage_id,
        type: fileType.match('image/*') ? 'photo' : 'video',
        status: 'new'
      };

      if (
        (fileItem.type === 'video' && !isAcceptVideo) ||
        !checkFileAccept(getFileType(file), accept)
      ) {
        dialogBackend.alert({
          message:
            messageAcceptFail ||
            i18n.formatMessage({ id: 'file_accept_type_fail' })
        });

        if (inputRef?.current) {
          inputRef.current.value = null;
        }

        break;
      }

      if (fileSize > maxSize && maxSize !== 0) {
        fileItem.max_size = maxSize;
        fileLimitItems.push(fileItem);
      } else {
        newItems.push(fileItem);
      }
    }

    if (newItems.length) {
      setValidFileItems(prev => [...(currentFiles || prev), ...newItems]);
    }

    if (fileLimitItems.length > 0) {
      dialogBackend.alert({
        message:
          fileLimitItems.length === 1
            ? i18n.formatMessage(
                { id: 'warning_upload_limit_one_file' },
                {
                  fileName: shortenFileName(fileLimitItems[0].file_name, 30),
                  fileSize: parseFileSize(fileLimitItems[0].file_size),
                  maxSize: parseFileSize(fileLimitItems[0]?.max_size)
                }
              )
            : i18n.formatMessage(
                { id: 'warning_upload_limit_multi_file' },
                {
                  numberFile: fileLimitItems.length,
                  photoMaxSize: parseFileSize(maxSizePhoto),
                  videoMaxSize: parseFileSize(maxSizeVideo)
                }
              )
      });
    }
  };

  return [validFileItems, setValidFileItems, handleFiles] as const;
}
