import { useGlobal } from '@metafox/framework';
import { AlbumItemProps } from '@metafox/photo/types';
import { ItemView, LineIcon } from '@metafox/ui';
import { Button, styled } from '@mui/material';
import React from 'react';

const name = 'AddPhotoProfile';

const AddMoreBtnWrapper = styled('div', {
  name,
  slot: 'AddMoreBtnWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  border: theme.mixins.border('primary'),
  borderRadius: theme.shape.borderRadius,
  height: theme.breakpoints.between('md', 'xl') ? 212 : '100%'
}));

const AddPhotoProfile = ({ wrapAs, wrapProps }: AlbumItemProps) => {
  const { redirectTo, i18n } = useGlobal();

  const handleClick = (to: string) => {
    redirectTo('/photo/album/add');
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid="addPhotoProfile"
      data-eid="addPhotoProfile"
      style={{ border: 'none' }}
    >
      <AddMoreBtnWrapper>
        <Button
          size="large"
          color="primary"
          startIcon={<LineIcon icon="ico-photos-plus-o" />}
          sx={{ fontWeight: 'bold' }}
          onClick={handleClick}
        >
          {i18n.formatMessage({ id: 'create_album' })}
        </Button>
      </AddMoreBtnWrapper>
    </ItemView>
  );
};

export default AddPhotoProfile;
