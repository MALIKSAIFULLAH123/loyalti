import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block'
      },
      showFull: {},
      multipleLine: {
        display: '-webkit-box',
        padding: '0',
        overflow: 'hidden',
        maxWidth: '100%',
        whiteSpace: 'normal',
        textOverflow: 'ellipsis',
        WebkitBoxOrient: 'vertical'
      },
      oneLine: {
        overflow: 'hidden',
        textOverflow: 'ellipsis',
        whiteSpace: 'nowrap',
        maxWidth: '100%'
      },
      fixHeight: {},
      noFixHeight: {},
      body1: {},
      body2: {},
      h1: {},
      h2: {},
      h3: {},
      h4: {},
      h5: {},
      h6: {},
      lines2: {
        WebkitLineClamp: 2,
        '&$body1': {
          maxHeight: `calc(${theme.typography.body1.lineHeight} * ${theme.typography.body1.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.body1.lineHeight} * ${theme.typography.body1.fontSize} * 2)`
          }
        },
        '&$body2': {
          maxHeight: `calc(${theme.typography.body2.lineHeight} * ${theme.typography.body2.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.body2.lineHeight} * ${theme.typography.body2.fontSize} * 2)`
          }
        },
        '&$h1': {
          maxHeight: `calc(${theme.typography.h1.lineHeight} * ${theme.typography.h1.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h1.lineHeight} * ${theme.typography.h1.fontSize} * 2)`
          }
        },
        '&$h2': {
          maxHeight: `calc(${theme.typography.h2.lineHeight} * ${theme.typography.h2.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h2.lineHeight} * ${theme.typography.h2.fontSize} * 2)`
          }
        },
        '&$h3': {
          maxHeight: `calc(${theme.typography.h3.lineHeight} * ${theme.typography.h3.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h3.lineHeight} * ${theme.typography.h3.fontSize} * 2)`
          }
        },
        '&$h4': {
          maxHeight: `calc(${theme.typography.h4.lineHeight} * ${theme.typography.h4.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h4.lineHeight} * ${theme.typography.h4.fontSize} * 2)`
          }
        },
        '&$h5': {
          maxHeight: `calc(${theme.typography.h5.lineHeight} * ${theme.typography.h5.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h5.lineHeight} * ${theme.typography.h5.fontSize} * 2)`
          }
        },
        '&$h6': {
          maxHeight: `calc(${theme.typography.h6.lineHeight} * ${theme.typography.h6.fontSize} * 2)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h6.lineHeight} * ${theme.typography.h6.fontSize} * 2)`
          }
        }
      },
      lines3: {
        WebkitLineClamp: 3,
        '&$body1': {
          maxHeight: `calc(${theme.typography.body1.lineHeight} * ${theme.typography.body1.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.body1.lineHeight} * ${theme.typography.body1.fontSize} * 3)`
          }
        },
        '&$body2': {
          maxHeight: `calc(${theme.typography.body2.lineHeight} * ${theme.typography.body2.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.body2.lineHeight} * ${theme.typography.body2.fontSize} * 3)`
          }
        },
        '&$h1': {
          maxHeight: `calc(${theme.typography.h1.lineHeight} * ${theme.typography.h1.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h1.lineHeight} * ${theme.typography.h1.fontSize} * 3)`
          }
        },
        '&$h2': {
          maxHeight: `calc(${theme.typography.h2.lineHeight} * ${theme.typography.h2.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h2.lineHeight} * ${theme.typography.h2.fontSize} * 3)`
          }
        },
        '&$h3': {
          maxHeight: `calc(${theme.typography.h3.lineHeight} * ${theme.typography.h3.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h3.lineHeight} * ${theme.typography.h3.fontSize} * 3)`
          }
        },
        '&$h4': {
          maxHeight: `calc(${theme.typography.h4.lineHeight} * ${theme.typography.h4.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h4.lineHeight} * ${theme.typography.h4.fontSize} * 3)`
          }
        },
        '&$h5': {
          maxHeight: `calc(${theme.typography.h5.lineHeight} * ${theme.typography.h5.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h5.lineHeight} * ${theme.typography.h5.fontSize} * 3)`
          }
        },
        '&$h6': {
          maxHeight: `calc(${theme.typography.h6.lineHeight} * ${theme.typography.h6.fontSize} * 3)`,
          '&$fixHeight': {
            height: `calc(${theme.typography.h6.lineHeight} * ${theme.typography.h6.fontSize} * 3)`
          }
        }
      }
    }),
  { name: 'TruncateText' }
);

export default useStyles;
