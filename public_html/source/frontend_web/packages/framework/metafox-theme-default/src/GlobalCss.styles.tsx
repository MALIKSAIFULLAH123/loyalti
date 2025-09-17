import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import { range } from 'lodash';

type StyleDict = Record<string, React.CSSProperties | string>;

function globalCss(global: StyleDict, theme: Theme) {
  // keep line break for help text
  global['img'] = {
    maxWidth: '100%'
  };
  global['iframe'] = {
    maxWidth: '100%'
  };
  global['.MuiButton-contained.MuiButton-colorInherit:hover'] = {
    backgroundColor: '#d5d5d5'
  };

  global['.middleAlign'] = {
    minHeight: '100%',
    display: 'flex',
    alignItems: 'center'
  };
  // p should margin 4px when empty because make large space on paragraph
  global['p:empty'] = {
    margin: 0
  };
  // multiple breakline
  global['p:empty + p:empty'] = {
    height: '12px'
  };

  global['.select-quick-sort'] = {
    padding: '0 8px',
    '&:hover': {
      background: 'rgba(0,0,0,0.04)'
    },
    '& .MuiInputBase-input': {
      padding: '5px 24px 5px 5px !important',
      fontWeight: 600,
      background: 'transparent !important'
    },
    '& .MuiSelect-icon': {
      right: '4px'
    },
    '& fieldset': {
      display: 'none'
    }
  };

  global['.select-sort-feed'] = {
    padding: 0,
    color: `${theme.palette.text.secondary} !important`,
    '&:hover': {
      background: 'rgba(0,0,0,0.04)'
    },
    '& .MuiInputBase-input': {
      padding: '5px 22px 5px 5px !important',
      fontWeight: theme.typography.fontWeightBold,
      background: 'transparent !important'
    },
    '& .MuiSelect-icon': {
      right: '4px'
    },
    '& .MuiIconButton-root': {
      right: '-7px',
      [theme.breakpoints.down('sm')]: {
        right: '-4px'
      }
    },
    '& fieldset': {
      display: 'none'
    }
  };

  global['.MuiDataGrid-root .MuiDataGrid-columnHeader:focus'] = global[
    '.MuiDataGrid-root .MuiDataGrid-cell:focus'
  ] = {
    outline: 'none'
  };

  // allow multiline message in alert, confirm dialog.
  // clean following lines whenever you find better solutions?
  global['.MuiDialogContentText-root'] = {
    whiteSpace: 'pre-line'
  };

  global['.separateHr > div+div:before'] = {
    content: '""',
    position: 'relative',
    display: 'block',
    borderTop: '1px solid #ededed',
    height: '20px'
  };

  global['.text-center'] = {
    textAlign: 'center'
  };

  global['.MuiDivider-root'] = {
    backgroundColor: '#cecece'
  };

  global['.withBackgroundStatus:after'] = {};
  global['.hidden'] = { display: 'none !important' };
  global['a'] = { textDecoration: 'none' };
  global['.dotSeparators > * + span:before'] = {
    content: '"·"',
    paddingLeft: '0.4em',
    paddingRight: '0.4em',
    fontWeight: 'normal',
    color: theme.palette.text.secondary
  };
  global['.dotSeparatorsAfter > span:not(:last-child):after'] = {
    content: '"·"',
    paddingLeft: '0.4em',
    paddingRight: '0.4em',
    fontWeight: 'normal',
    color: theme.palette.text.secondary
  };

  global['.typoSigInSocialite'] = {
    width: '100%',
    color: theme.palette.text.hint,
    fontSize: theme.mixins.pxToRem(15),
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    margin: `${theme.spacing(2, 0, 2, 0)}!important`,
    '&::before': {
      content: "''",
      flex: 1,
      borderTop: `1px solid ${theme.palette.text.hint}`,
      marginRight: theme.spacing(2)
    },
    '&::after': {
      content: "''",
      flex: 1,
      borderTop: `1px solid ${theme.palette.text.hint}`,
      marginLeft: theme.spacing(2)
    }
  };

  global['ul'] = {
    margin: 0,
    padding: 0,
    paddingLeft: '40px'
  };

  global['.editor-list-checklist li'] = {
    outline: 'none',
    listStyle: 'none',
    position: 'relative',
    '&::before': {
      content: "'✓'",
      marginRight: '4px',
      marginLeft: '-16px',
      display: 'inline-flex',
      position: 'absolute',
      top: 0,
      left: 0
    }
  };

  global['.table'] = {
    borderCollapse: 'collapse',
    borderSpacing: 0,
    overflowY: 'scroll',
    overflowX: 'scroll',
    tableLayout: 'fixed',
    width: 'max-content'
  };

  global['.tableCell'] = {
    border: `1px solid ${
      theme.palette.mode === 'light'
        ? theme.palette.grey[600]
        : theme.palette.grey[200]
    }`
  };

  // The pointer is not displayed on the comment (On Safari browser)
  global['.public-DraftEditor-content'] = {
    outline: '1px solid transparent !important'
  };

  global['.smartbanner-android'] = {
    background: '#f2f2f2',
    boxShadow: 'unset',
    '& .smartbanner-button': {
      boxShadow: 'unset'
    },
    '& .smartbanner-button-text': {
      borderRadius: theme.spacing(0.5),
      background: theme.palette.primary.main
    }
  };

  global['input::-webkit-outer-spin-button, input::-webkit-inner-spin-button'] =
    {
      WebkitAppearance: 'none',
      margin: 0
    };
  global['input[type=number]'] = {
    MozAppearance: 'textfield'
  };
  global['.grecaptcha-badge '] = {
    [theme.breakpoints.down('md')]: {
      bottom: `${theme.spacing(7)} !important`
    }
  };
}

function globalFlexGrow(global: StyleDict, theme: Theme) {
  range(1, 4).forEach(value => {
    global[`.flexGrow-${value}`] = { flex: value, minWidth: 0 };
  });
}

function globalMaxWidth(global: StyleDict, theme: Theme) {
  global['.maxWidth-md'] = { maxWidth: 720 };
}

function globalAvatarSize(global: StyleDict, theme: Theme) {
  const sizeMap = {
    xs: 3,
    sm: 4,
    md: 5,
    lg: 6,
    '10': 10,
    '20': 20,
    '21': 21
  };

  Object.keys(sizeMap).forEach(key => {
    const width = theme.spacing(sizeMap[key]);

    global[`.avatarSize-${key}`] = {
      width,
      height: width
    };
  });
}

function globalBgColor(global: StyleDict, theme: Theme) {
  global['.bgColor-paper'] = {
    backgroundColor: theme.palette.background.paper
  };
  global['.bgColor-auto'] = {
    backgroundColor:
      theme.themeId === 'paper' ? theme.palette.background.paper : 'transparent'
  };
  global['.bgColor-main'] = { backgroundColor: theme.palette.background.main };
  global['.bgColor-dark'] = { backgroundColor: theme.palette.background.dark };
  global['.bgColor-light'] = {
    backgroundColor: theme.palette.background.light
  };
}

function globalBorderRadius(global: StyleDict, theme: Theme) {
  global['.borderRadius-base'] = { borderRadius: theme.shape.borderRadius };
}

function globalSpacing(global: StyleDict, theme: Theme) {
  const props = {
    pl: 'paddingLeft',
    pt: 'paddingTop',
    pr: 'paddingRight',
    pb: 'paddingBottom',
    ml: 'marginLeft',
    mr: 'marginRight',
    mt: 'marginTop',
    mb: 'marginBottom'
  };

  Object.keys(props).forEach(key => {
    range(0, 8).forEach(value => {
      global[`.${key}${value}`] = {
        [props[key]]: `${theme.spacing(value)} !important`
      };
    });
  });
}

function globalBlockDivider(global: StyleDict, theme: Theme) {
  const props = theme.blockDivider || {};

  Object.keys(props).forEach(key => {
    global[`.blockDivider-${key}`] = { ...props[key] };
  });
}

function globalTypography(global: StyleDict, theme: Theme) {
  const props = theme.typography;

  Object.keys(props)
    .filter(x => props[x] && props[x].fontSize)
    .forEach(key => {
      global[`[role="option"] .option-${key}, .typography-${key}`] = {
        ...theme.typography[key]
      };
    });
}

const useGlobalStyles = makeStyles((theme: Theme) => {
  const global: StyleDict = {
    '.srOnly': { display: 'none' },
    '.overflowHidden': { overflow: 'hidden' }
  };

  [
    globalCss,
    globalTypography,
    globalBlockDivider,
    globalSpacing,
    globalFlexGrow,
    globalBgColor,
    globalMaxWidth,
    globalAvatarSize,
    globalBorderRadius
  ].forEach(x => x(global, theme));

  return createStyles({ '@global': global });
});

export default useGlobalStyles;
