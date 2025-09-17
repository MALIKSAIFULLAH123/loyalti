import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import { range } from 'lodash';

type StyleDict = Record<string, React.CSSProperties | string>;

function globalCss(global: StyleDict, theme: Theme) {
  // keep line break for help text

  global['.MuiButton-contained.MuiButton-colorInherit:hover'] = {
    backgroundColor: '#d5d5d5'
  };

  global['.middleAlign'] = {
    minHeight: '100%',
    display: 'flex',
    alignItems: 'center'
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

  global['.withBackgroundStatus:after'] = {
    content: '""',
    position: 'absolute',
    left: '0',
    top: '0',
    right: '0',
    bottom: '0',
    display: 'block',
    backgroundImage:
      'linear-gradient(0deg, rgba(0,0,0,0.4) 0, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.4) 100%)'
  };
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
