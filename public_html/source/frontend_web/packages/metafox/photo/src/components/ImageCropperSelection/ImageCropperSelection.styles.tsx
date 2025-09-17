import { makeStyles, createStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        top: 200,
        left: 200,
        width: 200,
        height: 200,
        position: 'absolute',
        transform: 'translate3d(0, 0, 0)',
        boxSizing: 'border-box',
        cursor: 'move',
        boxShadow: '0 0 0 9999em rgba(0, 0, 0, 0.5)',
        touchAction: 'manipulation',
        border: '1px solid rgba(255,255,255, 0.9)'
      },
      disabled: {
        cursor: 'inherit',
        '& $dragHandle': { cursor: 'inherit' }
      },
      circular: {
        borderRadius: '50%',
        boxShadow: '0px 0px 1px 1px white, 0 0 0 9999em rgba(0, 0, 0, 0.5)'
      },
      rootInvisible: {
        display: 'none'
      },
      dragHandle: {
        position: 'absolute',
        '&::after': {
          position: 'absolute',
          content: "''",
          display: 'block',
          width: '10px',
          height: '10px',
          borderRadius: '50%',
          backgroundColor: 'rgba(255,255,255, 0.5)',
          border: '1px solid rgba(255, 255, 255, 0.7)',
          boxSizing: 'border-box',
          outline: '1px solid transparent'
        }
      },
      ordNW: {
        top: '0',
        left: '0',
        marginTop: '-5px',
        marginLeft: '-5px',
        cursor: 'nw-resize',
        '&::after': { top: 0, left: 0 }
      },
      ordN: {
        top: '0',
        left: '50%',
        marginTop: '-5px',
        marginLeft: '-5px',
        cursor: 'n-resize',
        '&::after': { top: '0' }
      },
      ordNE: {
        top: '0',
        right: '0',
        marginTop: '-5px',
        marginRight: '-5px',
        cursor: 'ne-resize',
        '&::after': { top: '0', right: '0' }
      },
      ordE: {
        top: '50%',
        right: '0',
        marginTop: '-5px',
        marginRight: '-5px',
        cursor: 'e-resize',
        '&::after': { right: '0' }
      },
      ordSE: {
        bottom: '0',
        right: '0',
        marginBottom: '-5px',
        marginRight: '-5px',
        cursor: 'se-resize',
        '&::after': { bottom: '0', right: '0' }
      },
      ordS: {
        bottom: '0',
        left: '50%',
        marginBottom: '-5px',
        marginLeft: '-5px',
        cursor: 's-resize',
        '&::after': { bottom: '0' }
      },

      ordSW: {
        bottom: '0',
        left: '0',
        marginBottom: '-5px',
        marginLeft: '-5px',
        cursor: 'sw-resize',
        '&::after': { bottom: '0', left: '0' }
      },
      ordW: {
        top: '50%',
        left: '0',
        marginTop: '-5px',
        marginLeft: '-5px',
        cursor: 'w-resize',
        '&::after': { left: '0' }
      },
      dragBar: {
        position: 'absolute',

        '& $ordN': {
          top: '0',
          left: '0',
          width: '100%',
          height: '6px',
          marginTop: '-3px'
        },
        '& $ordE': {
          right: '0',
          top: '0',
          width: '6px',
          height: '100%',
          marginRight: '-3px'
        },
        '& $ordS': {
          bottom: '0',
          left: '0',
          width: '100%',
          height: '6px',
          marginBottom: '-3px'
        },
        '& $ordW': {
          top: '0',
          left: '0',
          width: '6px',
          height: '100%',
          marginLeft: '-3px'
        }
      },
      fixedAspect: {
        '& $dragHandle.$ordN': { display: 'none' },
        '& $dragHandle.$ordE': { display: 'none' },
        '& $dragHandle.$ordS': { display: 'none' },
        '& $dragHandle.$ordW': { display: 'none' }
      },
      '@media (pointer: coarse)': {
        ordN: { display: 'none' },
        ordE: { display: 'none' },
        ordS: { display: 'none' },
        ordW: { display: 'none' },
        dragHandle: { width: '24px', height: '24px' }
      }
    }),
  { name: 'ImageCropperSelection' }
);
