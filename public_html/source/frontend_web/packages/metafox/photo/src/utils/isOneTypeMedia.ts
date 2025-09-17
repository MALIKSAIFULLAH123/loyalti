import { isPhotoType, isVideoType } from '@metafox/utils';

export const isOneTypeMedia = (files: FileList | Blob[]) => {
  if (!files?.length) return true;

  let photosLength = 0;
  let videosLength = 0;

  for (let i = 0; i < files.length; ++i) {
    const type = files[i].type;

    if (isPhotoType(type)) photosLength += 1;

    if (isVideoType(type)) videosLength += 1;
  }

  if (photosLength > 0 && videosLength > 0) return false;

  return true;
};
