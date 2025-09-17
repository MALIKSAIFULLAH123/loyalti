import { useGetItems, useGlobal } from '@metafox/framework';
import { TodoItem } from '@metafox/gettingstarted/types';
import HtmlViewer from '@metafox/html-viewer';
import { ScrollContainer } from '@metafox/layout';
import { Image, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Grid, styled, Typography } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';

const ListSlider = styled(Box, { slot: 'ListSlider' })(({ theme }) => ({
  '& .slick-track': {
    display: 'flex',
    flexWrap: 'wrap',
    width: '100%',
    flexDirection: 'row',
    margin: 0
  },
  '& .slick-initialized .slick-slide': {
    display: 'flex',
    height: 'auto',
    '& > div': {
      width: '100%',
      height: '100%'
    }
  },
  '& .slick-arrow': {
    marginTop: '28%',
    top: 0
  },
  '& .slick-list': {
    cursor: 'pointer'
  },
  '& .slick-next': {
    display: 'flex!important',
    alignItems: 'center',
    width: '35px',
    height: '35px',
    right: '0px',
    fontSize: '24px',
    justifyContent: 'center',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    }
  },
  '& .slick-prev': {
    display: 'flex!important',
    alignItems: 'center',
    width: '35px',
    height: '35px',
    left: '0px',
    fontSize: '24px',
    justifyContent: 'center',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    }
  },
  '& .slick-dots li button:before': {
    fontSize: theme.mixins.pxToRem(12),
    color: theme.palette.primary.main
  },
  '& .slick-dots li.slick-active button:before': {
    color: theme.palette.primary.main
  },
  '& .slick-dots li': {
    width: theme.spacing(1)
  },
  '& .slick-dots': {
    bottom: 0,
    position: 'unset'
  }
}));

const NextRow = styled('div', { name: 'nextButton' })(({ theme }) => ({
  zIndex: 99,
  background: 'rgba(0,0,0,0.4)!important',
  color: '#fff!important',
  '& span': {
    padding: theme.spacing(1)
  },
  borderRadius: '50%'
}));

const Root = styled(Grid, { name: 'rootStep' })(({ theme }) => ({
  paddingBottom: theme.spacing(3),
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column-reverse',
    flexWrap: 'nowrap',
    height: '100%'
  }
}));

export default function StepGettingStarted({ item }: { item: TodoItem }) {
  const { dispatch, dialogBackend, isMobile } = useGlobal();
  const images = useGetItems(item?.attach_images);
  const [sliderKey, setSliderKey] = React.useState(item?.id);
  const sliderRef = React.useRef<Slider>(null);

  const settings = {
    dots: true,
    infinite: true,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: false,
    initialSlide: 0,
    nextArrow: (
      <NextRow>
        <LineIcon icon="ico-angle-right" />
      </NextRow>
    ),
    prevArrow: (
      <NextRow>
        <LineIcon icon="ico-angle-left" />
      </NextRow>
    )
  };

  React.useEffect(() => {
    if (item) {
      setSliderKey(item.id);
    }
  }, [item]);

  React.useLayoutEffect(() => {
    if (!item.is_done)
      dispatch({ type: 'gettingStarted/doneStep', payload: { id: item.id } });
  }, [dispatch, item]);

  const openDetail = image => {
    dialogBackend.present({
      component: 'gettingStarted.dialog.simplePhoto',
      props: {
        src: getImageSrc(image.image, '1024')
      }
    });
  };

  const isImage = !isEmpty(images);

  return (
    <Root container>
      <Grid
        item
        xs={12}
        sm={isImage ? 6 : 12}
        p={{ xs: 1, sm: 2 }}
        flex={1}
        minHeight={0}
      >
        <ScrollContainer
          autoHide
          autoHeightMin={isMobile ? 'unset' : '50vh'}
          {...(isMobile && { autoHeightMax: '100%', autoHeight: true })}
        >
          <Typography variant="h3">{item.title}</Typography>
          <HtmlViewer html={item.description} />
        </ScrollContainer>
      </Grid>
      {isImage && (
        <Grid
          item
          xs={12}
          sm={6}
          sx={{ alignSelf: 'center', width: '100%', flexBasis: 'auto' }}
        >
          <ListSlider p={{ xs: 1, sm: 2 }}>
            <Slider ref={sliderRef} {...settings} key={sliderKey}>
              {images.map((image, index) => (
                <Box key={index.toString()} onClick={() => openDetail(image)}>
                  <Image
                    key={index.toString()}
                    src={getImageSrc(image.image, '500')}
                    aspectRatio="169"
                    imageFit="cover"
                  />
                </Box>
              ))}
            </Slider>
          </ListSlider>
        </Grid>
      )}
    </Root>
  );
}
