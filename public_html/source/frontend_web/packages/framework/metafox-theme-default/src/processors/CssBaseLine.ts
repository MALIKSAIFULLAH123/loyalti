import { Theme } from '@mui/material';

export default function overridesGlobalStyles(theme: Theme) {
  if (!theme.components) {
    theme.components = {};
  }

  theme.components.MuiCssBaseline = {
    styleOverrides: {
      html: {
        WebkitFontSmoothing: 'auto',
        fontSize: '16px'
      },
      'grammarly-extension-vbars': {
        display: 'none!important'
      },
      body: {
        fontFamily: theme.fontFamily,
        overflowX: 'hidden',
        fontSize: '0.8125rem',
        WebkitTapHighlightColor: 'transparent',
        wordWrap: 'break-word',
        [theme.breakpoints.up('md')]: {
          minHeight: '100vh'
        }
      },
      '.MuiDialog-root.isLastDialog': {
        zIndex: '1300 !important'
      },
      a: {
        color: theme.palette.primary.main
      },
      '.MuiDialog-root .MuiBackdrop-root': {
        transition: 'none !important',
        opacity: '1 !important'
      },
      '.MuiDialog-root.notLastDialog .MuiBackdrop-root': {
        opacity: '1 !important'
      },
      '.MuiDialog-root.notLastDialog ~ .MuiDialog-root .MuiBackdrop-root': {
        opacity: '0 !important'
      },
      '.public-DraftEditorPlaceholder-root': {},
      // !important fix bug FOXSOCIAL5-1895
      '.public-DraftStyleDefault-block': {
        margin: '0 !important'
      },
      '.MuiMenu-paper, .MuiDialog-paperScrollPaper': {
        minWidth: '10rem'
      },
      // fix form in dialog css base line
      '.MuiDialog-paperScrollPaper > form': {
        display: 'flex',
        flexDirection: 'column',
        flex: 1,
        overflowY: 'auto'
      },
      '.invalid-feedback': {
        color: theme.palette.error.main,
        marginTop: theme.spacing(0.75)
      },
      '[role="button"]': {
        cursor: 'pointer'
      },
      'input::-webkit-outer-spin-button, input::-webkit-inner-spin-button': {
        WebkitAppearance: 'none',
        margin: 0
      },
      'input[type=number]': {
        MozAppearance: 'textfield'
      },
      'body.uiLayoutPreview': {
        '&::-webkit-scrollbar': {
          width: 5
        },
        '&::-webkit-scrollbar-track': {
          backgroundColor: '#ebebeb',
          webkitBorderRadius: 5,
          borderRadius: 5
        },
        '&::-webkit-scrollbar-thumb': {
          webkitBorderRadius: 5,
          borderRadius: 5,
          background: '#6d6d6d'
        }
      },
      '& .DraftEditor-editorContainer': {
        position: 'relative',
        zIndex: 1,
        '& figure': {
          margin: 0
        }
      },
      '& .mh-scroll-200': {
        maxHeight: '200px',
        overflowY: 'auto'
      }
    }
  };
}
