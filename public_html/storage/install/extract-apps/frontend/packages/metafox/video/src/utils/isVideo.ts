import { isVideoType } from '@metafox/utils';

export const isVideo = (files: FileList | Blob[]) => {
  if (!files?.length) return false;

  let videosLength = 0;

  // just accept 1 type for all files
  for (let i = 0; i < files.length; ++i) {
    const type = files[i].type;

    if (isVideoType(type)) videosLength += 1;
  }

  if (videosLength === files.length) return true;

  return false;
};
