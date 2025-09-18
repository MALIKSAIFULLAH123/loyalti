import React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { LAYOUT_EDITOR_TOGGLE, useGlobal } from '@metafox/framework';
import { Box, IconButton, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import { getImageSrc } from '@metafox/utils';
import { CreationTypeAdvertise } from '@metafox/advertise/types';
import { LineIcon } from '@metafox/ui';

export interface Props {
  [key: string]: any;
}

const ImageStyled = styled('img', {
  shouldForwardProp: props =>
    props !== 'slotName' && props !== 'width' && props !== 'height'
})<{ width?: any; height?: any }>(({ theme, width }) => ({
  objectFit: 'cover',
  maxWidth: '100%'
}));

const IconButtonStyled = styled(IconButton, {
  shouldForwardProp: props => props !== 'isTablet'
})<{ isTablet?: boolean }>(({ theme, isTablet }) => ({
  position: 'absolute',
  top: theme.spacing(1),
  right: theme.spacing(1),
  zIndex: 1,
  opacity: 0.7,
  ...(!isTablet && {
    visibility: 'hidden'
  })
}));

const ContentStyled = styled(Box, {
  shouldForwardProp: props => props !== 'slotName' && props !== 'isMobile'
})<{ slotName: string; isMobile?: boolean }>(
  ({ theme, slotName, isMobile }) => ({
    display: 'block',
    cursor: 'pointer',
    position: 'relative',
    '&:hover': {
      '#button-remove': {
        visibility: 'visible'
      }
    },
    // 728 x 90 – Leaderboard Banner
    '&:before': {
      content: '""',
      display: 'block',
      paddingBottom: '12.5%'
    },
    ...(slotName === 'side' && {
      // 300 x 250 – Medium Rectangle
      '&:before': {
        content: '""',
        display: 'block',
        paddingBottom: '80%'
      }
    }),
    ...(isMobile && {
      // 320 x 50 – Mobile Leaderboard
      '&:before': {
        content: '""',
        display: 'block',
        paddingBottom: '15.625%'
      }
    })
  })
);

const WrapperImg = styled('div')(({ theme }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  '& img': {
    width: '100%',
    height: '100%',
    maxWidth: '100%'
  }
}));

function Base(props: Props) {
  const { slotName, identity } = props;
  const {
    assetUrl,
    dispatch,
    i18n,
    localStore,
    useIsMobile,
    jsxBackend,
    useGetItem
  } = useGlobal();
  const data = useGetItem(identity);
  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);
  const isMobile = useIsMobile();
  const isTablet = useIsMobile(true);

  const slotItem = React.useMemo(() => {
    let result = slotName || 'main';

    if (['subside', 'side'].includes(slotName)) {
      result = 'side';
    }

    if (['main'].includes(slotName)) {
      result = 'main';
    }

    return result;
  }, [slotName]);

  const dimension = React.useMemo(() => {
    let result = 'origin';

    if (['side'].includes(slotItem)) {
      result = '500';
    }

    if (['main'].includes(slotItem)) {
      result = '1024';
    }

    if (isMobile) {
      result = '500';
    }

    return result;
  }, [slotItem, isMobile]);

  if (isEmpty(data) || data?.creation_type !== CreationTypeAdvertise.IMAGE)
    return null;

  const imgSrc = getImageSrc(
    data?.image,
    dimension,
    assetUrl('advertise.default_ad_thumbnail')
  );

  const handleUpdateClick = () => {
    window.open(data.destination_url);
    dispatch({ type: 'advertise/updateTotalClick', payload: data });
  };

  const hideItem = () => {
    if (isEditMode) return;

    dispatch({ type: 'advertise/hideItem', payload: data });
  };

  const { extra } = data;

  const InViewImpression = jsxBackend.get('advertise.ui.inViewImpression');

  return (
    <Block testid="advertiseBlockBanner">
      <BlockContent>
        <InViewImpression item={data} />
        <ContentStyled slotName={slotItem} isMobile={isMobile}>
          <WrapperImg>
            <ImageStyled
              src={imgSrc}
              title={data?.image_values?.image_tooltip || ''}
              onClick={handleUpdateClick}
            />
          </WrapperImg>
          {extra?.can_hide ? (
            <IconButtonStyled
              id="button-remove"
              size="smallest"
              onClick={hideItem}
              disabled={isEditMode}
              isTablet={isTablet}
              variant="blacked"
              title={i18n.formatMessage({ id: 'remove' })}
            >
              <LineIcon icon="ico-close" />
            </IconButtonStyled>
          ) : null}
        </ContentStyled>
      </BlockContent>
    </Block>
  );
}

export default Base;
