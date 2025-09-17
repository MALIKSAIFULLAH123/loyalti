/**
 * @type: service
 * name: LayoutPreviewContainer
 */
/* eslint-disable id-length */
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import devices from '../DeviceList';

type PreviewContainerProps = {
  previewDevice: string;
  url: string;
};

const Root = styled('div', {
  name: 'PreviewDv',
  slot: 'Root',
  shouldForwardProp: prop => prop !== 'tablet'
})<{ tablet: boolean; imageUrl: string }>(({ tablet, imageUrl }) => ({
  display: 'flex',
  alignItems: 'center',
  minHeight: '100vh',
  backgroundImage: `url(${imageUrl})`,
  ...(tablet && {
    alignItems: 'baseline',
    height: '100vh',
    '& .PreviewDv-wrapper': {
      marginTop: 16,
      transformOrigin: 'top',
      padding: '90px 25px'
    },
    '& .PreviewDv-topBar, .PreviewDv-bottomBar': {
      display: 'none'
    }
  })
}));

const Wrapper = styled('div', {
  name: 'PreviewDv',
  slot: 'Wrapper',
  shouldForwardProp: prop => prop !== 'landscape'
})<{ landscape: boolean }>(({ landscape }) => ({
  position: 'relative',
  margin: '0 auto',
  padding: '105px 24px',
  borderRadius: 56,
  backgroundColor: '#464646',
  boxSizing: 'content-box',
  boxShadow: 'inset 0 0 3px 0 rgba(0,0,0,0.7)',
  ...(landscape && {
    padding: '26px 112px'
  }),
  ':before': {
    content: '""',
    background: '#080808',
    width: 'calc(100% - 12px)',
    height: 'calc(100% - 12px)',
    position: 'absolute',
    top: 6,
    left: 6,
    borderRadius: 50,
    zIndex: 1
  },
  ':after': {
    content: '""',
    boxShadow: 'inset 0 0 3px 0 rgba(0,0,0,0.1), inset 0 0 6px 3px #212121',
    width: 'calc(100% - 16px)',
    height: 'calc(100% - 16px)',
    position: 'absolute',
    top: 8,
    left: 8,
    borderRadius: 48,
    zIndex: 2
  }
}));

const Screen = styled('div', {
  name: 'PreviewDv',
  slot: 'screen'
})({
  width: '100%',
  height: '100%',
  borderRadius: 1,
  position: 'relative',
  zIndex: 3,
  background: 'linear-gradient(to bottom, #b3b3b7 0%, #424044 100%)'
});

const TopBar = styled('div', {
  name: 'PreviewDv',
  slot: 'topBar'
})({
  background: '#212121',
  height: 14,
  position: 'absolute',
  top: 68,
  left: 0,
  width: '100%'
});

const BottomBar = styled('div', {
  name: 'PreviewDv',
  slot: 'bottomBar'
})({
  background: '#212121',
  height: 14,
  position: 'absolute',
  bottom: 68,
  left: 0,
  width: '100%'
});

const IFrame = styled('iframe', {
  name: 'PreviewDv',
  slot: 'iframe'
})({
  border: 'none',
  margin: 0,
  padding: 0
});

export default function PreviewContainer({
  previewDevice,
  url
}: PreviewContainerProps) {
  const { assetUrl } = useGlobal();
  const preset = devices.find(x => x.value === previewDevice);
  const actualSize = preset ? preset.size : '768x1024';
  const [iw, ih] = actualSize.split('x').map(x => parseInt(x, 10));
  const isTablet = 768 <= iw;
  const scaleRatio = (window.innerHeight - 32) / 1204;
  const imageUrl = assetUrl('layout.image_preview');

  return (
    <Root tablet={isTablet} imageUrl={imageUrl}>
      <Wrapper
        landscape={iw > ih}
        style={
          isTablet
            ? { transform: `scale(${scaleRatio}`, width: iw, height: ih }
            : undefined
        }
      >
        <Screen>
          <IFrame
            width={iw}
            height={ih}
            name="layoutPreviewWindow"
            title="Mobile Preview"
            src={url}
          />
        </Screen>
        <TopBar />
        <BottomBar />
      </Wrapper>
    </Root>
  );
}
