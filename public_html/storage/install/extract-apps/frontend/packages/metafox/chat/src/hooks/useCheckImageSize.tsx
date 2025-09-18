import { useGlobal, BasicFileItem } from '@metafox/framework';
import React from 'react';
import { isEmpty } from 'lodash';
import {
  shortenFileName,
  parseFileSize,
  checkFileAccept
} from '@metafox/utils';
type Props = {
  initialValues?: BasicFileItem[];
  messageAcceptFail?: string;
  inputRef?: React.MutableRefObject<HTMLInputElement>;
};

export default function useCheckImageSize(props?: Props) {
  const { initialValues, messageAcceptFail, inputRef } = props || {};
  const { dialogBackend, i18n, getSetting } = useGlobal();
  const [validFileItems, setValidFileItems] = React.useState<BasicFileItem[]>(
    initialValues || []
  );

  const errFormat = React.useRef(false);

  const MaxSize: any = getSetting(
    'core.attachment.maximum_file_size_each_attachment_can_be_uploaded'
  );
  const MaxFile: any = getSetting(
    'core.attachment.maximum_number_of_attachments_that_can_be_uploaded'
  );

  const clearInput = () => {
    if (inputRef?.current) {
      inputRef.current.value = null;
    }
  };

  const handleFiles = (files: any, isOnlyValidate = false) => {
    if (isEmpty(files)) return;

    const newItems = [];
    const fileLimitItems = [];
    const totalFile =
      (isOnlyValidate ? 0 : validFileItems.length) + files?.length;

    if (MaxFile < totalFile) {
      dialogBackend.alert({
        title: 'Oops!',
        message: i18n.formatMessage(
          { id: 'could_not_select_more_than_limit_files' },
          {
            limit: MaxFile
          }
        )
      });

      return;
    }

    for (let index = 0; index < files.length; ++index) {
      const file = files[index];
      const fileSize = file.size;
      const fileItem: BasicFileItem = {
        file_name: file.name,
        file_size: file.size,
        file_type: file.type,
        file
      };

      if (!checkFileAccept(file?.type, 'image/*')) {
        dialogBackend
          .alert({
            message:
              messageAcceptFail ||
              i18n.formatMessage({ id: 'file_accept_type_fail' })
          })
          .then(() => (errFormat.current = false))
          .catch(() => (errFormat.current = false));
        errFormat.current = true;

        clearInput();

        break;
      }

      if (fileSize > MaxSize && MaxSize !== 0) {
        fileItem.max_size = MaxSize;
        fileLimitItems.push(fileItem);
      } else {
        newItems.push(fileItem.file);
      }
    }

    if (errFormat.current) return;

    if (newItems.length) {
      setValidFileItems([...(validFileItems || []), ...newItems]);
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
                { id: 'warning_upload_limit_multi_image' },
                {
                  numberFile: fileLimitItems.length,
                  photoMaxSize: parseFileSize(MaxSize)
                }
              )
      });

      clearInput();

      return;
    }

    clearInput();

    return true;
  };

  return [validFileItems, setValidFileItems, handleFiles] as const;
}
