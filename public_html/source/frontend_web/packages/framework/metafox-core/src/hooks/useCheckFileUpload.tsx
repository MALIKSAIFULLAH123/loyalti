/**
 * @type: service
 * name: useCheckFileUpload
 */
import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import {
  shortenFileName,
  isVideoType,
  parseFileSize,
  getFileType
} from '@metafox/utils';
type Props = {
  maxSizeLimit: Record<string, any>;
  accept?: any;
};

function validateFileInputType(file, accept) {
  const type = getFileType(file);

  // accept all file
  if (!accept) return true;

  const MIMEtype = new RegExp(accept.replace('*', '.*').replaceAll(',', '|'));

  return MIMEtype.test(type);
}
export default function useCheckFileUpload(props: Props) {
  const { maxSizeLimit: maxSizeLimitProps, accept } = props || {};
  const { dialogBackend, i18n, useLimitFileSize } = useGlobal();
  const maxSizeGlobal = useLimitFileSize();
  const maxSizeLimit = maxSizeLimitProps || maxSizeGlobal;
  const maxSizePhoto = maxSizeLimit?.photo ?? maxSizeLimit?.other ?? 0;
  const maxSizeVideo = maxSizeLimit?.video ?? maxSizeLimit?.other ?? 0;

  const handleFiles = files => {
    const validItems = [];

    if (isEmpty(files)) return;

    const fileLimitItems = [];

    for (let index = 0; index < files.length; ++index) {
      const file = files[index];
      const fileSize = file.size;
      const maxSize = isVideoType(file?.type) ? maxSizeVideo : maxSizePhoto;

      if (!validateFileInputType(file, accept)) {
        dialogBackend.alert({
          message: i18n.formatMessage({ id: 'file_accept_type_fail' })
        });

        break;
      }

      if (fileSize > maxSize && maxSize !== 0) {
        file.max_size = maxSize;
        fileLimitItems.push(file);
        break;
      }

      validItems.push(file);
    }

    if (fileLimitItems.length > 0) {
      dialogBackend.alert({
        message:
          fileLimitItems.length === 1
            ? i18n.formatMessage(
                { id: 'warning_upload_limit_one_file' },
                {
                  fileName: shortenFileName(fileLimitItems[0].name, 30),
                  fileSize: parseFileSize(fileLimitItems[0].size),
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

    return validItems;
  };

  return [handleFiles] as const;
}
