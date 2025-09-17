import { useGlobal } from '@metafox/framework';
import { shortenFileName, parseFileSize } from '@metafox/utils';
import React from 'react';
import useFeedMediaConfig from './useFeedMediaConfig';
import { checkFileMediaFeed } from '@metafox/photo/utils';
import { uniqWith, concat } from 'lodash';

export default function useHandleFeedMediaFile(
  extra?: Record<string, any>
): [(a: File[], b: File[], c: (x: File) => void) => void] {
  const { dialogBackend, i18n } = useGlobal();
  const config = useFeedMediaConfig({ parentUser: extra?.parentUser });
  const {
    maxSizePhoto,
    maxSizeVideo,
    limitTotal,
    canUploadVideo,
    canUploadPhoto,
    acceptTypes
  } = config || {};

  const handleChange = React.useCallback(
    async (files, currentFiles, callback) => {
      if (!files.length) return;

      // check limit count file
      if (limitTotal > 0) {
        // limit 0 is unlimit
        const totalFileUid = currentFiles?.length
          ? currentFiles.filter(x => x.uid !== undefined).length
          : 0;
        const totalFile = files.length + totalFileUid;

        if (totalFile > limitTotal) {
          return dialogBackend.alert({
            message: i18n.formatMessage(
              { id: 'maximum_per_upload_limit_reached' },
              {
                limit: limitTotal
              }
            )
          });
        }
      }

      const [fileItems, fileLimit, fileNotAccept] = checkFileMediaFeed({
        files,
        canUploadVideo,
        canUploadPhoto,
        maxSizeVideo,
        maxSizePhoto,
        accept: acceptTypes
      });
      const value = uniqWith(
        concat(currentFiles, fileItems),
        (arrVal, othVal) => {
          return arrVal?.uid && othVal?.uid && arrVal.uid === othVal.uid;
        }
      ).filter(Boolean);

      if (fileNotAccept.length) {
        if (callback) {
          callback(value);
        }

        return dialogBackend.alert({
          message: i18n.formatMessage({ id: 'file_accept_type_fail' })
        });
      }

      if (fileLimit.length) {
        if (callback) {
          callback(value);
        }

        const msgValue =
          fileLimit.length === 1
            ? {
                fileName: shortenFileName(fileLimit[0].name, 30),
                fileSize: parseFileSize(fileLimit[0].size),
                maxSize: parseFileSize(fileLimit[0]?.maxSize)
              }
            : {
                numberFile: fileLimit.length,
                photoMaxSize: parseFileSize(maxSizePhoto),
                videoMaxSize: parseFileSize(maxSizeVideo)
              };
        const msgId =
          fileLimit.length === 1
            ? 'warning_upload_limit_one_file'
            : 'warning_upload_limit_multi_file';

        return dialogBackend.alert({
          message: i18n.formatMessage({ id: msgId }, msgValue)
        });
      }

      if (callback) {
        callback(value);
      }

      return fileItems;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [
      maxSizePhoto,
      maxSizeVideo,
      dialogBackend,
      i18n,
      canUploadVideo,
      canUploadPhoto
    ]
  );

  return [handleChange];
}
