import { MediaRatioVariant } from '@metafox/framework';
import { styled } from '@mui/material/styles';

const map = {
  '169': '56.25%',
  '32': '66.6666%',
  '23': '150%',
  '43': '75%',
  '34': '133.33%',
  '11': '100%',
  '165': '31.25%'
};

const StyledSkeleton = styled('div', {
  name: 'ImageSkeleton',
  shouldForwardProp: prop => prop !== 'ratio' && prop !== 'borderRadius'
})<{ ratio: MediaRatioVariant; borderRadius?: number }>(
  ({ theme, ratio, borderRadius = 1 }) => ({
    boxSizing: 'border-box',
    height: 'auto',
    display: 'block',
    width: '100%',
    animation: 'animation-c7515d 1.5s ease-in-out 0.5s infinite',
    backgroundColor: 'rgba(5, 5, 5, 0.11)',
    paddingBottom: ratio && map[ratio] ? map[ratio] : ratio,
    borderRadius:
      theme.shape.borderRadius * borderRadius ? theme.spacing(borderRadius) : 0
  })
);

export default StyledSkeleton;
