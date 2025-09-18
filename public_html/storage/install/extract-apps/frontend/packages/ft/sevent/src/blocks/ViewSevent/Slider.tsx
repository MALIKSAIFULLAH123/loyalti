import React from 'react';
import { useTheme } from '@mui/material/styles';
import Box from '@mui/material/Box';
import KeyboardArrowLeft from '@mui/icons-material/KeyboardArrowLeft';
import KeyboardArrowRight from '@mui/icons-material/KeyboardArrowRight';
import SwipeableViews from 'react-swipeable-views';
import { autoPlay } from 'react-swipeable-views-utils';
import { styled } from '@mui/material';
import { getImageSrc } from '@metafox/utils';
import {
    useGlobal
  } from '@metafox/framework';

const AutoPlaySwipeableViews = autoPlay(SwipeableViews);

function CustomSlider({ item, attachedPhotos }) {
  const theme = useTheme();
  const {
    assetUrl
  } = useGlobal();

  const images = [];

  if (item.iframeVideo) {
    images.push({
      imgPath: item.iframeVideo,
      type: 'iframe'
    });
  }
  
  const cover = (item?.image ? getImageSrc(item?.image) : assetUrl('sevent.no_image'));

  if (!item.image) {
    if (!item.iframeVideo && attachedPhotos.length == 0)
      images.push({
        imgPath: cover
      });
  } else {
    images.push({
        imgPath: cover
      });
  }

  if (attachedPhotos) {
    attachedPhotos.forEach((photo) => {
      images.push({
        imgPath: photo.image.origin
      });
    });
  }

  const [activeStep, setActiveStep] = React.useState(0);
  const maxSteps = images.length;

  const handleNext = () => {
    setActiveStep((prevActiveStep) => (prevActiveStep + 1) % maxSteps);
  };

  const handleBack = () => {
    setActiveStep((prevActiveStep) => (prevActiveStep - 1 + maxSteps) % maxSteps);
  };

  const SliderItem = styled('div', { name: 'ActionBox' })(({ theme }) => ({
    position: 'relative',
    paddingBottom: '56.25%',
    overflow: 'hidden'
  }));

  return (
    <Box sx={{ maxWidth: '100%', flexGrow: 1 }}>
      <AutoPlaySwipeableViews
        axis={theme.direction === 'rtl' ? 'x-reverse' : 'x'}
        index={activeStep}
        interval={500000}
        onChangeIndex={(index) => setActiveStep(index)}
        enableMouseEvents
      >
        {images.map((step, index) => (
            <div key={index}>
                {Math.abs(activeStep - index) <= 2 ? (
                step.type === 'iframe' ? (
                    <SliderItem dangerouslySetInnerHTML={{ __html: step.imgPath }} />
                ) : (
                    <SliderItem key={index}>
                        <div style={{ backgroundImage: `url(${step.imgPath})`, 
                            width: '100%', height: '100%', backgroundSize: 'cover', 
                            backgroundPosition: 'center', 
                            position: 'absolute' }} />
                    </SliderItem>
                )
                ) : null}
            </div>
        ))}
      </AutoPlaySwipeableViews>
      {maxSteps > 1 && (
        <Box display='flex' gap='5px' alignItems={'center'} 
            justifyContent='center' style={{ marginTop: '16px' }}>
            <div>
                <a onClick={handleBack} style={{ color: theme.palette.text.secondary, cursor: 'pointer' }}>
                    <KeyboardArrowLeft />
                </a>
                <a onClick={handleNext} style={{ color: theme.palette.text.secondary, cursor: 'pointer' }}>
                    <KeyboardArrowRight />
                </a>
            </div>
         </Box>
        )}
    </Box>
  );
}

export default React.memo(CustomSlider);
