/**
 * @type: ui
 * name: Loading
 */
import { styled } from '@mui/material/styles';
import React from 'react';
import LoadingSVG from './LoadingSVG.svg';
import LoadingSVGDark from './LoadingSVGDark.svg';
import { useGlobal, DEFAULT_THEME_TYPE } from '@metafox/framework';
import { detect } from 'detect-browser';

type LoadingVariant = 'init' | 'absolute' | 'fixed' | 'relative' | undefined;

interface LoadingProps {
  related?: boolean;
  center?: boolean;
  absolute?: boolean;
  variant?: LoadingVariant;
}

const LoadingRoot = styled('div', {
  name: 'Loading',
  slot: 'Root',
  shouldForwardProp: prop =>
    prop !== 'related' && prop !== 'darkMode' && prop !== 'center'
})<{
  darkMode?: boolean;
  variant?: LoadingVariant;
  related?: boolean;

  center?: boolean;
  absolute?: boolean;
}>(({ theme, variant, darkMode }) => ({
  ...(variant === 'absolute' && {
    display: 'flex',
    justifyContent: 'center',
    minHeight: '100%',
    position: 'absolute',
    left: '0',
    top: '0',
    right: '0',
    bottom: '0',
    alignItems: 'center',
    backgroundColor: darkMode ? process.env.MFOX_LOADING_BG : '#fff'
  }),
  ...(variant === 'fixed' && {
    display: 'flex',
    justifyContent: 'center',
    minHeight: '100%',
    position: 'fixed',
    left: '0',
    top: '0',
    right: '0',
    bottom: '0',
    alignItems: 'center',
    backgroundColor: darkMode ? process.env.MFOX_LOADING_BG : '#fff',
    zIndex: 99,
    opacity: 0.5
  }),
  ...(variant === 'init' && {
    display: 'flex',
    justifyContent: 'center',
    minHeight: '100%',
    position: 'fixed',
    left: '0',
    top: '0',
    right: '0',
    bottom: '0',
    alignItems: 'center',
    backgroundColor: darkMode ? process.env.MFOX_LOADING_BG : '#fff',
    zIndex: 99
  }),
  ...(variant === 'relative' && {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: darkMode ? process.env.MFOX_LOADING_BG : '#fff'
  })
}));

export default function Loading({
  related,
  center,
  absolute,
  variant
}: LoadingProps) {
  const { usePreference } = useGlobal();
  const { themeType: _preferThemeType } = usePreference();
  const themeTypeMode = _preferThemeType ?? DEFAULT_THEME_TYPE;
  const darkMode = themeTypeMode === 'dark';
  const browser = detect();

  const { name } = browser || {};

  if (!variant) {
    if (absolute) {
      variant = 'absolute';
    } else if (related && center) {
      variant = 'relative';
    }
  }

  React.useEffect(() => {
    if (window?.document && name === 'ios') {
      const bodyOverflow = window.document.body.style.overflow;
      const overlayLoading =
        bodyOverflow !== 'hidden' && (!variant || variant === 'fixed');

      if (overlayLoading) {
        window.document.body.style.overflow = 'hidden';
      }

      return () => {
        if (overlayLoading) {
          window.document.body.style.overflow = bodyOverflow;
        }
      };
    }
  }, []);

  return (
    <LoadingRoot
      variant={variant || 'fixed'}
      darkMode={darkMode}
      data-testid="loadingIndicator"
    >
      <div style={{ width: '5rem', height: '5rem' }}>
        {darkMode ? <LoadingSVGDark /> : <LoadingSVG />}
      </div>
    </LoadingRoot>
  );
}
