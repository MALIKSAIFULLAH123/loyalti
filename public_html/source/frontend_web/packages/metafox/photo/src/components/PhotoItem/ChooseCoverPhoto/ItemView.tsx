import { PhotoItemProps } from '@metafox/photo/types';
import { Image, ItemView } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { CircularProgress, styled, Box } from '@mui/material';
import React from 'react';
import { useGlobal } from '@metafox/framework';

const name = 'ChooseAvatarCoverPhoto';

const LoadingStyled = styled('div', { name })(({ theme }) => ({
  position: 'absolute',
  height: '100%',
  width: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  color: theme.palette.background.paper,
  zIndex: 1,
  '&:before': {
    content: '""',
    position: 'absolute',
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: theme.palette.action.active
  }
}));

const Root = styled(Box, {
  name,
  slot: 'Root',
  shouldForwardProp: props => props !== 'isMature'
})<{ isMature?: boolean }>(({ theme, isMature }) => ({
  minWidth: 130,
  cursor: isMature ? 'not-allowed' : 'pointer',
  transition: 'all 0.2s ease',
  position: 'relative',
  opacity: isMature ? '0.5' : '1',
  '&:hover': {
    boxShadow: theme.shadows[4],
    opacity: isMature ? '0.3' : '0.8'
  },
  minHeight: 150
}));

const PhotoChooseCoverItem = ({
  item,
  identity,
  itemProps,
  wrapAs,
  wrapProps
}: PhotoItemProps) => {
  const [loading, setLoading] = React.useState<boolean>(false);
  const { dialogBackend, i18n } = useGlobal();

  if (!item) return null;

  const cover = getImageSrc(item.image);
  const { onSuccess, close } = itemProps;
  const { mature } = item;
  const isMature = mature !== 0;

  const handleChoose = () => {
    if (isMature) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'you_cannot_choose_a_mature_photo' })
      });

      return;
    }

    setLoading(true);
    close && close();
    onSuccess && onSuccess(item);
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <Root isMature={isMature}>
        {loading && (
          <LoadingStyled data-testid="loadingIndicator">
            <CircularProgress color="inherit" />
          </LoadingStyled>
        )}
        <Box onClick={handleChoose}>
          <Image
            src={cover}
            aspectRatio={'43'}
            alt={item.title}
            identity={identity}
          />
        </Box>
      </Root>
    </ItemView>
  );
};

export default PhotoChooseCoverItem;
