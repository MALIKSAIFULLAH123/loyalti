import { useGlobal } from '@metafox/framework';
import { get, isNil } from 'lodash';
// eslint-disable-next-line @typescript-eslint/no-unused-vars
import React from 'react';

const checkCanUploadPhoto = ({ setting, acl, profileSettings }) => {
  return (
    get(acl, 'photo.photo.create') &&
    get(setting, 'feed.types.photo_set.can_create_feed') &&
    (!isNil(profileSettings?.photo_share_photos)
      ? profileSettings.photo_share_photos
      : true)
  );
};

const checkCanUploadVideo = ({ setting, acl, profileSettings }) => {
  return (
    get(acl, 'video.video.create') &&
    get(setting, 'feed.types.photo_set.can_create_feed') &&
    get(setting, 'video.video_service_is_ready') &&
    get(acl, 'video.video.upload_video_file') &&
    (!isNil(profileSettings?.video_share_videos)
      ? profileSettings.video_share_videos
      : true)
  );
};

const filterAcceptType = (
  { setting, acl, profileSettings },
  { acceptImage, acceptVideo }
) => {
  const acceptTypes = [];

  if (
    checkCanUploadPhoto({
      setting,
      acl,
      profileSettings
    })
  ) {
    acceptTypes.push(acceptImage);
  }

  if (
    checkCanUploadVideo({
      setting,
      acl,
      profileSettings
    })
  ) {
    acceptTypes.push(acceptVideo);
  }

  return { acceptTypes: acceptTypes.join(',') };
};

export default function useFeedMediaConfig(config): Record<string, any> {
  const { useLimitFileSize, getAcl, getSetting } = useGlobal();
  const MAX_SIZE_FILE = useLimitFileSize();
  const maxSizePhoto = MAX_SIZE_FILE?.photo ?? MAX_SIZE_FILE?.other ?? 0;
  const maxSizeVideo = MAX_SIZE_FILE?.video ?? MAX_SIZE_FILE?.other ?? 0;
  const acl = getAcl();
  const setting = getSetting();
  const limitTotal: number = get(
    acl,
    'photo.photo.maximum_number_of_media_per_upload'
  );

  const acceptImage = (
    getSetting<string[]>('core.file_mime_type_accepts.image') || ['image/*']
  ).join();
  const acceptVideo = (
    getSetting<string[]>('core.file_mime_type_accepts.video') || ['video/*']
  ).join();

  const canUploadVideo = checkCanUploadVideo({
    setting,
    acl,
    profileSettings: config?.parentUser?.profile_settings
  });

  const canUploadPhoto = checkCanUploadPhoto({
    setting,
    acl,
    profileSettings: config?.parentUser?.profile_settings
  });
  const { acceptTypes } = filterAcceptType(
    {
      setting,
      acl,
      profileSettings: config?.parentUser?.profile_settings
    },
    { acceptImage, acceptVideo }
  );

  return {
    maxSizePhoto,
    maxSizeVideo,
    limitTotal,
    canUploadVideo,
    canUploadPhoto,
    acceptTypes
  };
}
