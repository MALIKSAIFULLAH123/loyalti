import React, { useCallback } from 'react';
import { StoreProductItemShape } from '@metafox/core/types';
import { Flag, DotSeparator, FormatNumberCompact, LineIcon } from '@metafox/ui';
import { useGetItem, useGlobal, RouteLink } from '@metafox/framework';
import { Box, Typography, CardMedia, Grid, styled } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';
import MoreInfo from './MoreInfo/Base';
import Summary from './Summary';
import Rating from './Rating';
import Purchase from './Purchase';
import { LoadingButton } from '@mui/lab';
import { isEmpty } from 'lodash';

const GridStyled = styled(Grid, { name: 'gridStyled' })(({ theme }) => ({
  width: '100%',
  marginLeft: 0,
  marginTop: 0
}));

const GridItemStyled = styled(Grid, { name: 'gridItemStyled' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(2),
    '& > div': {
      marginBottom: theme.spacing(3)
    }
  })
);

const CardContentStyled = styled(Box, { name: 'cardContentStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    width: 'calc(100% - 120px)',
    paddingLeft: theme.spacing(2),
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column',
      alignItems: 'flex-start'
    }
  })
);

const CardImageWrapper = styled(Box, { name: 'cardImageWrapper' })(
  ({ theme }) => ({ display: 'flex', alignItems: 'center', pl: 1, pb: 1 })
);

const SliderWrapper = styled(Box, { name: 'sliderWrapper' })(({ theme }) => ({
  margin: theme.spacing(2, 0),
  border: '1px solid',
  borderColor: theme.palette.border.secondary
}));

const Install = styled(Box, { name: 'Install' })(({ theme }) => ({
  textAlign: 'right',
  [theme.breakpoints.down('sm')]: {
    paddingTop: theme.spacing(1),
    display: 'flex',
    flexDirection: 'column',
    textAlign: 'left',
    '& button': {
      marginRight: theme.spacing(1)
    }
  }
}));

const NextRow = styled(Box, { name: 'nextButton' })(({ theme }) => ({
  zIndex: 99,
  background: 'rgba(0,0,0,0.5) !important',
  color: 'white !important',
  borderColor: 'none !important',
  width: '48px !important',
  height: '48px !important',
  display: 'inline-flex !important',
  alignItems: 'center !important',
  justifyContent: 'center !important',
  transition: 'all 300ms ease',
  '&:hover': {
    background: 'rgba(0,0,0,0.7) !important'
  },
  '&:before': {
    display: 'none !important'
  },
  '& span': {
    padding: theme.spacing(1),
    fontSize: '24px'
  }
}));

export const ProductContext = React.createContext<StoreProductItemShape>(null);

export default function AdminAppStoreShowDetail() {
  const { usePageParams, dispatch, assetUrl, i18n, jsxBackend } = useGlobal();

  const { identity, id } = usePageParams();

  const item = useGetItem<StoreProductItemShape>(identity);

  const handleInstall = useCallback(() => {
    if (item?.is_installing) return;

    dispatch({ type: 'app/store/install', payload: { identity } });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id, item]);

  if (!item)
    return (
      <Box
        display="flex"
        sx={{
          height: '100px',
          width: '100%',
          backgroundColor: 'background.paper',
          justifyContent: 'center'
        }}
      >
        {jsxBackend.render({ component: 'form.DefaultLoading' })}
      </Box>
    );

  const { author, installation_status } = item;
  const is_installing = installation_status === 'installing';
  const settings = {
    infinite: true,
    speed: 400,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    nextArrow: (
      <NextRow sx={{ right: '0 !important', left: 'auto !important' }}>
        <LineIcon icon="ico-angle-right" />
      </NextRow>
    ),
    prevArrow: (
      <NextRow sx={{ left: '0 !important', right: 'auto !important' }}>
        <LineIcon icon="ico-angle-left" />
      </NextRow>
    )
  };

  const images = item?.images?.length
    ? item?.images
    : [assetUrl('layout.image_no_results')];
  const enableInstall = item?.is_installed
    ? item?.can_upgrade
    : item?.can_install;

  return (
    <ProductContext.Provider value={item}>
      <GridStyled container spacing={2} sx={{ background: '#fff' }}>
        <Grid xs={12} sm={12} md={8} lg={8} item sx={{ pr: 2 }}>
          <Box sx={{ display: 'flex' }}>
            <Box sx={{ display: 'flex', width: '100%' }}>
              <CardImageWrapper>
                <CardMedia
                  component="img"
                  sx={{ width: 120, height: 120, borderRadius: 1 }}
                  image={getImageSrc(
                    item?.icon || item?.image,
                    '500',
                    assetUrl('layout.site_apple_touch_icon')
                  )}
                />
              </CardImageWrapper>
              <CardContentStyled>
                <Box sx={{ maxWidth: { sm: '70%', xs: '100%' } }}>
                  <Flag type={'is_featured'} value={item?.is_featured} />
                  <Typography
                    component="div"
                    variant="h2"
                    sx={{ fontWeight: 500 }}
                    color="#555555"
                  >
                    {item?.name}
                  </Typography>
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    component="div"
                  >
                    <DotSeparator>
                      {item?.categories?.map((category, index) => (
                        <span key={index}>{category.name}</span>
                      ))}
                    </DotSeparator>{' '}
                    <RouteLink target="_blank" to={author.url}>
                      <Typography
                        variant="body2"
                        color="text.secondary"
                        component="span"
                      >
                        {author.name}
                      </Typography>
                    </RouteLink>
                  </Typography>
                </Box>
                <Install>
                  <LoadingButton
                    disableRipple
                    disableFocusRipple
                    onClick={handleInstall}
                    variant="contained"
                    disabled={
                      !enableInstall ||
                      is_installing ||
                      isEmpty(item?.version_detail) ||
                      item?.version === 'N/A'
                    }
                    size="small"
                    loading={is_installing}
                  >
                    {item.label_install}
                  </LoadingButton>
                  <Typography
                    color="text.secondary"
                    variant="body2"
                    sx={{ py: 1 }}
                  >
                    {i18n.formatMessage(
                      { id: 'total_amount_installed' },
                      {
                        value: (
                          <FormatNumberCompact value={item?.total_installed} />
                        )
                      }
                    )}
                  </Typography>
                  {!isEmpty(item?.internal_admincp_url) && (
                    <RouteLink to={item?.internal_admincp_url?.url}>
                      {item?.internal_admincp_url?.label}
                    </RouteLink>
                  )}
                </Install>
              </CardContentStyled>
            </Box>
          </Box>
          <SliderWrapper>
            <Slider {...settings}>
              {images.map((image, index) => (
                <CardMedia
                  key={index}
                  component="img"
                  sx={{ height: '320px', width: '100%', objectFit: 'contain' }}
                  image={getImageSrc(image, '1024')}
                />
              ))}
            </Slider>
          </SliderWrapper>
          <MoreInfo />
        </Grid>
        <GridItemStyled xs={12} sm={12} md={4} lg={4} item>
          <Purchase />
          <Rating />
          <Summary />
        </GridItemStyled>
      </GridStyled>
    </ProductContext.Provider>
  );
}
