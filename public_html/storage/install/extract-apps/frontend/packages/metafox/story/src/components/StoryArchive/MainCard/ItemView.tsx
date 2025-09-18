import { useGlobal } from '@metafox/framework';
import { STORY_IMAGE_RADIO } from '@metafox/story/constants';
import { StoryItemProps } from '@metafox/story/types';
import { LineIcon, ItemView } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Typography, styled } from '@mui/material';
import moment from 'moment';
import * as React from 'react';

const name = 'StoryMainCard';

const WrapperItemView = styled(Box, {
  name,
  slot: 'WrapperBox',
  shouldForwardProp: props => props !== 'height'
})<{ height?: number }>(({ theme, height }) => ({
  width: '100%',
  paddingBottom: `${STORY_IMAGE_RADIO * 100}%`,
  position: 'relative',
  '&:before': {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: 0,
    boxShadow: '0px 20px 55px 20px rgba(0,0,0,0.5)',
    content: '""',
    zIndex: 2
  },
  '&:after': {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 0,
    boxShadow: '0px 20px 55px 50px rgba(0,0,0,0.5)',
    content: '""',
    zIndex: 2
  },
  '&:hover': {
    cursor: 'pointer'
  }
}));

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  height: '100%',
  width: '100%'
}));

const ImageUrl = styled('div', {
  name: 'PreviewDv',
  slot: 'Root',
  shouldForwardProp: prop => prop !== 'imageUrl'
})<{ imageUrl: string }>(({ theme, imageUrl }) => ({
  display: 'flex',
  alignItems: 'center',
  height: '100%',
  width: '100%',
  borderRadius: theme.shape.borderRadius,
  backgroundImage: `url(${imageUrl})`,
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center'
}));

const DateBox = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  zIndex: 3,
  padding: theme.spacing(1),
  color: '#fff',
  fontWeight: theme.typography.fontWeightBold
}));

const TotalView = styled(Box)(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  left: 0,
  padding: theme.spacing(1),
  display: 'flex',
  zIndex: 3,
  alignItems: 'center'
}));

const ToggleIconStyled = styled(LineIcon)(({ theme }) => ({
  color: '#fff',
  marginRight: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(14),
  fontWeight: theme.typography.fontWeightMedium
}));

const ViewTextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  fontSize: theme.mixins.pxToRem(14)
}));

export default function StoryArchiveItem({
  item,
  wrapAs,
  wrapProps
}: StoryItemProps) {
  const { assetUrl, navigate, useSession } = useGlobal();

  const { user } = useSession();

  if (!item?.image || !item) return null;

  const { image, statistic, creation_date } = item || {};

  const imageUrl = getImageSrc(image, '240', assetUrl('story.no_image'));

  const handleClick = () => {
    navigate(`/story-archive/${user?.id}/${item?.id}?date=${creation_date}`);
  };

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="story-archive-card">
      <WrapperItemView onClick={handleClick}>
        <WrapperImage>
          <ImageUrl key={imageUrl} imageUrl={imageUrl} />
        </WrapperImage>
        {creation_date ? (
          <DateBox>{moment(creation_date).format('ll')}</DateBox>
        ) : null}
        {statistic?.total_view ? (
          <TotalView>
            <ToggleIconStyled icon="ico-eye-o" />
            <ViewTextStyled variant="h5" color={'text.primary'}>
              {statistic?.total_view}
            </ViewTextStyled>
          </TotalView>
        ) : null}
      </WrapperItemView>
    </ItemView>
  );
}

StoryArchiveItem.displayName = 'StoryArchiveMainCard';
