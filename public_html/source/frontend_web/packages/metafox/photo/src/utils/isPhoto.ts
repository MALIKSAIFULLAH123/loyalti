import { isPhotoType, isVideoType } from '@metafox/utils';

export const isPhoto = (
  files: FileList | Blob[],
  allowVideos: boolean = true,
  allowPhotos: boolean = true
) => {
  if (!files?.length) return false;

  let photosLength = 0;

  // just accept 1 type for all files
  for (let i = 0; i < files.length; ++i) {
    const type = files[i].type;

    if (
      (allowPhotos && isPhotoType(type)) ||
      (allowVideos && isVideoType(type))
    )
      photosLength += 1;
  }

  if (photosLength === files.length) return true;

  return false;
};
