import { isVideoType, isPhotoType, getFileType } from '@metafox/utils';
import { uniqueId } from 'lodash';

type FileError = File & { maxSize: number };
type FileItem = { uid: string; source: string; file: File; status?: string };

function validateFileInputType(file, accept) {
  const type = getFileType(file);

  // accept all file
  if (!accept) return true;

  const MIMEtype = new RegExp(accept.replace('*', '.*').replaceAll(',', '|'));

  return MIMEtype.test(type);
}

export const checkFileMediaFeed = ({
  files,
  canUploadPhoto,
  canUploadVideo,
  maxSizeVideo,
  maxSizePhoto,
  accept
}) => {
  const fileItems: FileItem[] = [];
  const fileLimit: FileError[] = [];
  const fileNotAccept: FileError[] = [];

  for (let i = 0; i < files.length; ++i) {
    const fileItem = files instanceof FileList ? files.item(i) : files[i];
    const fileItemSize = fileItem.size;

    if (
      !validateFileInputType(fileItem, accept) ||
      (!canUploadVideo && isVideoType(fileItem?.type)) ||
      (!canUploadPhoto && isPhotoType(fileItem?.type))
    ) {
      fileNotAccept.push(fileItem);
      continue;
    }

    const maxSize = isVideoType(fileItem?.type) ? maxSizeVideo : maxSizePhoto;

    if (fileItemSize > maxSize && maxSize !== 0) {
      fileItem.maxSize = maxSize;
      fileLimit.push(fileItem);
      continue;
    }

    fileItems.push({
      uid: uniqueId('file'),
      source: URL.createObjectURL(fileItem),
      file: fileItem,
      status: 'new'
    });
  }

  return [fileItems, fileLimit, fileNotAccept];
};
