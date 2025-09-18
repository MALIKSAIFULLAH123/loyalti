import React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { useGlobal, Link, LAYOUT_EDITOR_TOGGLE } from '@metafox/framework';
import { Box, IconButton, styled, Typography } from '@mui/material';
import { isEmpty } from 'lodash';
import { getImageSrc } from '@metafox/utils';
import { CreationTypeAdvertise } from '../../types';
import { LineIcon, TruncateText } from '@metafox/ui';

export interface Props {
  [key: string]: any;
}

const ImageStyled = styled('img', {
  shouldForwardProp: props =>
    props !== 'slotName' && props !== 'width' && props !== 'height'
})<{ slotName: string; width?: any; height?: any }>(
  ({ theme, width, slotName }) => ({
    width: '90px',
    height: '90px',
    borderRadius: theme.shape.borderRadius,
    objectFit: 'cover'
  })
);

const ContentWrapperStyled = styled(Box, {
  shouldForwardProp: props => props !== 'slotName'
})<{ slotName: string }>(({ theme, slotName }) => ({
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center',
  position: 'relative',
  '&:hover': {
    '#button-remove': {
      visibility: 'visible'
    }
  }
}));

const ContentStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flex: 1,
  minWidth: 0,
  paddingRight: theme.spacing(1),
  cursor: 'pointer'
}));

const InfoStyled = styled(Box)(({ theme }) => ({
  marginLeft: theme.spacing(1)
}));

const TitleStyled = styled(Link)(({ theme }) => ({
  cursor: 'pointer'
}));

const DescriptionStyled = styled(Typography)(({ theme }) => ({}));

const IconButtonStyled = styled(IconButton, {
  shouldForwardProp: props => props !== 'isTablet'
})<{ isTablet?: boolean }>(({ theme, isTablet }) => ({
  position: 'absolute',
  top: theme.spacing(-0.5),
  right: theme.spacing(-0.5),
  zIndex: 1,
  opacity: 0.9,
  ...(!isTablet && {
    visibility: 'hidden'
  })
}));

function Base(props: Props) {
  const { slotName, identity } = props;

  const {
    assetUrl,
    i18n,
    dispatch,
    localStore,
    jsxBackend,
    useGetItem,
    useIsMobile
  } = useGlobal();
  const data = useGetItem(identity);
  const isTablet = useIsMobile(true);

  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);

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

  if (isEmpty(data) || data?.creation_type !== CreationTypeAdvertise.HTML)
    return null;

  const { image, html_values, extra } = data;

  const imgSrc = getImageSrc(
    image,
    'origin',
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

  const InViewImpression = jsxBackend.get('advertise.ui.inViewImpression');

  return (
    <Block testid="advertiseBlockHtml">
      <BlockContent>
        <InViewImpression item={data} />
        <ContentWrapperStyled slotName={slotItem}>
          <ContentStyled onClick={handleUpdateClick}>
            <ImageStyled slotName={slotItem} src={imgSrc} />
            <InfoStyled>
              <TruncateText lines={2}>
                <TitleStyled underline="none" color="primary">
                  {html_values?.html_title}
                </TitleStyled>
              </TruncateText>
              <TruncateText lines={2}>
                <DescriptionStyled>
                  {html_values?.html_description}
                </DescriptionStyled>
              </TruncateText>
            </InfoStyled>
          </ContentStyled>
          {extra?.can_hide ? (
            <IconButtonStyled
              id="button-remove"
              size="smallest"
              onClick={hideItem}
              disabled={isEditMode}
              isTablet={isTablet}
              variant="itemActionIcon"
              title={i18n.formatMessage({ id: 'remove' })}
            >
              <LineIcon icon="ico-close" />
            </IconButtonStyled>
          ) : null}
        </ContentWrapperStyled>
      </BlockContent>
    </Block>
  );
}

export default Base;
