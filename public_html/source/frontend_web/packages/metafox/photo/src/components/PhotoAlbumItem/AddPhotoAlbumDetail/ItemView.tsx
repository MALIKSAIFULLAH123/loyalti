import { useGlobal } from '@metafox/framework';
import { APP_PHOTO, RESOURCE_ALBUM } from '@metafox/photo/constant';
import React from 'react';
import ButtonAdd from './ButtonAdd';

const AddPhotoAlbumDetail = ({ size = 'small' }) => {
  const { usePageParams } = useGlobal();
  const pageParams = usePageParams();

  return (
    <ButtonAdd
      size={size}
      identity={`${APP_PHOTO}.entities.${RESOURCE_ALBUM}.${
        pageParams?.profile_page ? pageParams?.album_id : pageParams.id
      }`}
    />
  );
};

export default AddPhotoAlbumDetail;
