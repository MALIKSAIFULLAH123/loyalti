import { styled } from '@mui/material/styles';
import React from 'react';
/* eslint-disable react/prop-types */

const name = 'Scrollbar';

export const Container = styled('div', {
  name,
  slot: 'root'
})({
  position: 'relative',
  overflow: 'hidden',
  width: '100%',
  height: '100%'
});

export const View = styled('div', {
  name,
  slot: 'content',
  shouldForwardProp: prop => prop !== 'scrollBarWidth'
})<{ scrollBarWidth: number }>(({ theme, scrollBarWidth }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  overflow: 'scroll',
  height: `calc(100% + ${scrollBarWidth}px)`,
  WebkitOverflowScrolling: 'touch',
  [theme.breakpoints.down('sm')]: {
    maxHeight: `calc(100% + ${scrollBarWidth}px)`
  },
  direction: 'ltr',
  marginRight: `-${scrollBarWidth}px`,
  marginLeft: 0
}));

export const HorizontalTrack = styled('div', {
  name,
  slot: 'horizontalTrack',
  shouldForwardProp: prop => prop !== 'hide'
})<{ hide?: boolean }>(({ hide }) => ({
  right: '2px',
  bottom: '2px',
  left: '2px',
  borderRadius: '3px',
  position: 'absolute',
  height: 6,
  transition: 'opacity 200ms',
  ...(hide && { display: 'none' })
}));

export function renderViewDefault(props) {
  return <div {...props} />;
}

export function renderTrackHorizontalDefault(props) {
  return <HorizontalTrack {...props} />;
}

export const VerticalTrack = styled('div', {
  name,
  slot: 'verticalTrack'
})({
  right: '2px',
  bottom: '2px',
  top: '2px',
  borderRadius: '3px',
  position: 'absolute',
  width: 6,
  transition: 'opacity 200ms'
});

export function renderTrackVerticalDefault(props) {
  return <VerticalTrack {...props} />;
}

export const HorizontalThumb = styled('div', {
  name: 'Scrollbar',
  slot: 'horizontalThumb'
})({
  cursor: 'pointer',
  borderRadius: 'inherit',
  backgroundColor: 'rgba(0,0,0,.2)',
  position: 'relative',
  display: 'block',
  height: '100%'
});

export function renderThumbHorizontalDefault(props) {
  return <HorizontalThumb {...props} />;
}

export const VerticalThumb = styled('div', { name, slot: 'verticalThumb' })({
  cursor: 'pointer',
  borderRadius: 'inherit',
  backgroundColor: 'rgba(0,0,0,.2)',
  position: 'relative',
  display: 'block',
  width: '100%'
});

export function renderThumbVerticalDefault(props) {
  return <VerticalThumb {...props} />;
}
