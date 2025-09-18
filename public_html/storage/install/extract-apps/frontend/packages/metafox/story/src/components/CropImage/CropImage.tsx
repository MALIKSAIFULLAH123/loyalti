import { useGlobal } from '@metafox/framework';
import { MAXZOOM, MINZOOM } from '@metafox/story/constants';
import { LineIcon } from '@metafox/ui';
import { Box, Button, Slider, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'DropAction';

const RootStyled = styled(Box, {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
  minHeight: '52px',
  height: '52px',
  color: '#fff',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  '&> p:first-of-type': {
    fontSize: theme.mixins.pxToRem(17)
  }
}));

const ControlStyled = styled(Box, {
  name,
  slot: 'ControlStyled'
})(({ theme }) => ({
  padding: theme.spacing(1),
  display: 'flex'
}));

const SliderContainer = styled(Box, {
  name,
  slot: 'SliderContainer'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: '250px',
  maxWidth: '400px'
}));

const BtnControl = styled(Button, {
  name,
  slot: 'BtnControl'
})(({ theme }) => ({
  background: 'none',
  boxShadow: 'none',
  padding: 0,
  fontSize: theme.mixins.pxToRem(13),
  minWidth: theme.spacing(4),
  color: '#fff !important',
  width: theme.spacing(4),
  height: theme.spacing(4),
  borderRadius: theme.spacing(4),
  '&:hover': {
    background: 'rgba(5, 5, 5, 0.04)',
    color: '#fff',
    boxShadow: 'none'
  }
}));

const BtnContainer = styled(Box, {
  name,
  slot: 'BtnContainer'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  '& button': {
    backgroundColor: theme.palette.background.paper,
    whiteSpace: 'nowrap',
    fontWeight: 'normal',
    color: theme.palette.text.primary,
    height: '36px',
    '&:hover': {
      backgroundColor: theme.palette.background.paper,
      color: theme.palette.text.primary
    }
  }
}));

const SliderZoom = styled(Slider, {
  name,
  slot: 'SliderZoom'
})(({ theme }) => ({
  margin: theme.spacing(0, 1),
  color: '#fff',
  '& .MuiSlider-rail': {
    backgroundColor: '#fff'
  },
  '& .MuiSlider-thumb': {
    backgroundColor: '#fff'
  },
  '& .MuiSlider-track': {
    border: 'none',
    backgroundColor: theme.palette.primary.main
  }
}));

interface Props {
  open: boolean;
  fire: any;
  state: any;
}

export const getMousePoint = (e: MouseEvent | React.MouseEvent) => ({
  x: Number(e.clientX),
  y: Number(e.clientY)
});

function DropImage({ open, fire, state }: Props) {
  const { i18n } = useGlobal();

  const { zoom } = state || {};

  const handleRotation = () => {
    const rotateNumber = 360 <= state.rotation + 90 ? 0 : state.rotation + 90;

    fire({ type: 'setRotation', payload: rotateNumber });
  };

  if (open) {
    return (
      <RootStyled>
        <ControlStyled>
          <SliderContainer>
            <BtnControl
              variant="text"
              onClick={() =>
                fire({
                  type: 'setZoom',
                  payload: { mode: 'minus' }
                })
              }
            >
              <LineIcon icon={'ico-minus'} />
            </BtnControl>
            <SliderZoom
              value={zoom}
              min={MINZOOM}
              max={MAXZOOM}
              step={0.1}
              aria-labelledby="Zoom"
              onChange={(e, zoom) =>
                fire({
                  type: 'setZoom',
                  payload: { zoom: zoom as number }
                })
              }
            />
            <BtnControl
              variant="text"
              onClick={() =>
                fire({
                  type: 'setZoom',
                  payload: { mode: 'plus' }
                })
              }
            >
              <LineIcon icon={'ico-plus'} />
            </BtnControl>
          </SliderContainer>
        </ControlStyled>
        <BtnContainer>
          <Button
            disableRipple
            startIcon={<LineIcon icon={'ico-rotate-right-alt'} />}
            onClick={handleRotation}
          >
            {i18n.formatMessage({ id: 'rotate' })}
          </Button>
        </BtnContainer>
      </RootStyled>
    );
  }

  return (
    <RootStyled>
      <Typography>
        {i18n.formatMessage({ id: 'select_photo_tp_crop_and_rotate' })}
      </Typography>
    </RootStyled>
  );
}

export default DropImage;
