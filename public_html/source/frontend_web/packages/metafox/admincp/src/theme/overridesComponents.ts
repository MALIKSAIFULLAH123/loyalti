import { Theme } from '@mui/material';

export default function overridesComponents(theme: Theme): void {
  // https://v4.mui.com/api/button/#css
  if (!theme.components) {
    theme.components = {};
  }

  theme.components.MuiChip = {
    defaultProps: {
      variant: 'outlined'
    },
    styleOverrides: {
      root: {
        fontWeight: 600,
        lineHeight: '18px',
        fontSize: '13px',
        backgroundColor:
          theme.palette.mode === 'dark'
            ? theme.palette.action.selected
            : 'transparent',
        '& .MuiIcon-root, & .MuiChip-deleteIcon': {
          marginRight: 8,
          color: theme.palette.primary.main,
          ':hover': {
            color: theme.palette.primary.main
          }
        }
      }
    }
  };

  theme.components.MuiTruncateText = {
    defaultProps: {
      component: 'div'
    }
  };

  theme.components.MuiPaper = {
    defaultProps: {
      elevation: 4
    }
  };

  theme.components.MuiGrid = {
    styleOverrides: {
      item: {}
    }
  };

  theme.components.SidebarAppMenu = {
    styleOverrides: {
      menuItemButton: {
        margin: theme.spacing(2)
      }
    }
  };

  theme.components.MuiToolbar = {
    styleOverrides: {
      root: {
        [theme.breakpoints.up('sm')]: {
          paddingLeft: theme.spacing(2),
          paddingRight: theme.spacing(2)
        }
      }
    }
  };

  theme.components.MuiTooltip = {
    defaultProps: {
      enterDelay: 2000
    }
  };

  theme.components.MuiAutocomplete = {
    styleOverrides: {
      root: {
        color: '#fff',
        '& .MuiOutlinedInput-root.MuiInputBase-sizeSmall': {
          paddingTop: '0 !important',
          paddingBottom: 0,
          '& .MuiAutocomplete-input': {
            height: 13,
            padding: '13.5px 0px 13.5px 14px'
          }
        },
        '& .MuiInputLabel-root.Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: `${theme.palette.text.hint} !important`
          })
        },
        '& .MuiOutlinedInput-root.Mui-disabled .MuiOutlinedInput-notchedOutline':
          {
            ...(theme.palette.mode === 'dark' && {
              borderColor: 'rgba(255, 255, 255, 0.23) !important'
            })
          },
        '& .MuiAutocomplete-popupIndicator.Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: 'rgba(255, 255, 255, 0.54) !important'
          })
        }
      }
    }
  };

  theme.components.MuiLink = {
    defaultProps: {
      color: 'inherit',
      underline: 'hover'
    },
    styleOverrides: {
      root: {
        '&[disabled]': {
          color: theme.palette.text.disabled,
          pointerEvents: 'none'
        }
      },
      underlineHover: {
        cursor: 'pointer',
        '&.MuiButton-text': {
          fontWeight: `${theme.typography.fontWeightRegular} !important`
        },
        '&.MuiButton-text:hover': {
          textDecoration: 'underline !important'
        }
      }
    }
  };

  theme.typography.fontFamily = theme.fontFamily;
  theme.typography.button = {
    fontFamily: theme.fontFamily
  };
  theme.components.MuiFeedEmbedHost = {
    styleOverrides: {
      root: {
        fontWeight: theme.typography.fontWeightBold,
        marginTop: theme.spacing(2)
      }
    }
  };

  theme.components.MuiItemView = {
    defaultProps: {},
    styleOverrides: {}
  };

  theme.components.MuiTypography = {
    defaultProps: {
      variantMapping: {
        subtitle1: 'h3'
      }
    },
    variants: [
      {
        props: { color: 'textPrimary' },
        style: { color: theme.palette.text.primary }
      },
      {
        props: { color: 'textDisabled' },
        style: { color: theme.palette.text.disabled }
      },
      {
        props: { color: 'textSecondary' },
        style: { color: theme.palette.text.secondary }
      },
      {
        props: { color: 'textHint' },
        style: { color: theme.palette.text.hint }
      }
    ]
  };
  // https://mui.com/api/tooltip/#css
  theme.components.MuiTooltip = {
    defaultProps: {
      arrow: true,
      placement: 'top'
    }
  };

  // https://mui.com/api/avatar/#css
  theme.components.MuiAvatar = {
    defaultProps: {
      variant: 'circular'
    }
  };

  // https://mui.com/api/app-bar/#css
  theme.components.MuiAppBar = {
    styleOverrides: {
      root: {
        height: theme.appBarHeight.normal ?? 58,
        boxShadow: 'none',
        borderBottom: '1px solid',
        borderColor:
          theme.palette.mode === 'light'
            ? 'rgba(0, 0, 0, 0.1)'
            : theme.palette.divider,
        backgroundImage: 'none',
        '& .MuiToolbar-root': {
          minHeight: 0,
          maxHeight: theme.appBarHeight.normal ?? 58,
          height: theme.appBarHeight.normal ?? 58
        }
      }
    }
  };

  theme.components.MuiMenuItem = {
    defaultProps: {
      component: 'div',
      disableRipple: true,
      disableTouchRipple: true
    },
    styleOverrides: {
      root: {}
    },
    variants: [
      {
        props: { variant: 'contained' },
        style: {
          borderRadius: theme.shape.borderRadius,
          margin: theme.spacing(0, 1),
          padding: theme.spacing(1.5, 1),
          '&:hover': {
            background: theme.palette.action.hover
          },
          '& .MuiListItemIcon-root': {
            color: '#828080',
            width: 32,
            height: 32,
            lineHeight: theme.mixins.pxToRem(32),
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '1rem',
            minWidth: '32px !important',
            textAlign: 'center',
            marginRight: 16,
            borderRadius: '50%',
            backgroundColor: '#e0dddd'
          },
          '& .MuiListItemText-primary': {
            fontWeight: theme.typography.fontWeightMedium
          },
          '& .MuiListItemText-secondary': {
            color: theme.palette.text.secondary,
            fontSize: '0.9375rem',
            paddingTop: 4,
            overflow: 'hidden',
            textOverflow: 'ellipsis'
          },
          '&.Mui-selected': {
            background: 'transparent',
            '&:hover': {
              background: theme.palette.action.selected
            },
            '& .MuiListItemIcon-root': {
              backgroundColor: theme.palette.primary.main,
              color: '#fff'
            },
            '& .MuiListItemText-primary': {
              color: theme.palette.primary.main
            }
          }
        }
      }
    ]
  };

  theme.components.MuiListItem = {
    defaultProps: {
      component: 'div'
    },
    styleOverrides: {},
    variants: [
      {
        props: { variant: 'contained' },
        style: {
          flex: 1,
          cursor: 'pointer',
          width: 'auto',
          borderRadius: theme.shape.borderRadius,
          margin: theme.spacing(0, 1),
          paddingTop: theme.spacing(1.5),
          paddingBottom: theme.spacing(1.5),
          paddingLeft: theme.spacing(1),
          paddingRight: theme.spacing(1),
          '&:hover': {
            background: theme.palette.action.hover
          },
          '& .MuiListItemIcon-root': {
            color: '#828080',
            width: 32,
            height: 32,
            lineHeight: theme.mixins.pxToRem(32),
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '1rem',
            minWidth: '32px !important',
            textAlign: 'center',
            marginRight: 16,
            borderRadius: '50%',
            backgroundColor: '#e0dddd'
          },
          '& .MuiListItemText-root': {
            marginTop: 0,
            marginBottom: 0
          },
          '& .MuiListItemText-primary': {
            fontWeight: theme.typography.fontWeightMedium,
            color: theme.palette.text.primary
          },
          '& .MuiListItemText-secondary': {
            color: theme.palette.text.secondary,
            marginTop: '0.25em',
            fontSize: theme.mixins.pxToRem(13),
            overflow: 'hidden',
            textOverflow: 'ellipse'
          },
          '&.Mui-selected': {
            background: 'transparent',
            '&:hover': {
              background: theme.palette.action.selected
            },
            '& .MuiListItemIcon-root': {
              backgroundColor: theme.palette.primary.main,
              color: '#fff'
            },
            '& .MuiListItemText-primary': {
              color: theme.palette.primary.main
            }
          }
        }
      },
      {
        props: { variant: 'dropdown' },
        style: {
          paddingTop: '6px',
          paddingBottom: '6px',
          cursor: 'pointer',
          '&:hover': {
            backgroundColor: theme.palette.action.selected
          }
        }
      }
    ]
  };

  theme.components.MuiListItemAvatar = {
    styleOverrides: {},
    variants: [
      {
        props: { variant: 'contained' },
        style: {
          '& .MuiListItemAvatar-root': {
            minWidth: 'auto'
          }
        }
      }
    ]
  };

  theme.components.MuiListItemText = {
    styleOverrides: {}
  };

  // https://mui.com/material-ui/api/switch/#css
  theme.components.MuiSwitch = {
    styleOverrides: {
      root: {
        '&.Mui-disabled': {
          pointerEvents: 'auto'
        }
      }
    }
  };

  // https://mui.com/api/button/#css
  theme.components.MuiButton = {
    styleOverrides: {
      root: {
        borderRadius: 4,
        textTransform: 'none',
        boxShadow: 'none',
        '&:hover': {
          boxShadow: 'none'
        },
        ...(theme.palette.mode === 'dark' && {
          color: '#FFF'
        }),
        '&.Mui-disabled': {
          ...(theme.palette.mode === 'dark' && {
            color: theme.palette.text.hint
          })
        }
      },
      contained: {},
      outlined: {
        padding: '7px 12px',
        fontSize: '15px',
        backgroundColor: 'transparent',
        ...(theme.palette.mode === 'dark' && {
          '&:not(:hover)': {
            borderColor: '#FFF'
          },
          '&:hover': {
            backgroundColor: theme.palette.grey[700],
            borderColor: '#FFF'
          },
          '&:disabled': {
            borderColor: theme.palette.text.hint
          }
        })
      },
      sizeSmall: {
        height: 32,
        fontSize: '13px',
        fontWeight: theme.typography.fontWeightBold
      },
      sizeMedium: {
        fontSize: '15px',
        fontWeight: theme.typography.fontWeightBold,
        height: 40
      },
      sizeLarge: {
        fontSize: '18px',
        fontWeight: theme.typography.fontWeightBold,
        height: 48
      },
      containedSizeSmall: {
        padding: '5px 10px'
      },
      outlinedSizeSmall: {
        padding: '5px 10px'
      },
      containedSizeLarge: {
        padding: '11px 16px'
      },
      outlinedSizeLarge: {
        padding: '11px 16px'
      },
      iconSizeSmall: {
        fontSize: '13px'
      },
      startIcon: {
        '&>*:nth-of-type(1)': {
          fontSize: 'inherit'
        }
      },
      endIcon: {}
    },
    variants: [
      {
        props: { size: 'smaller' },
        style: {
          fontSize: '13px',
          height: 24
        }
      },
      {
        props: { textTransform: 'uppercase' },
        style: {
          textTransform: 'uppercase'
        }
      },
      {
        props: {
          color: 'default'
        },
        style: {
          color: theme.palette.text.primary,
          backgroundColor:
            theme.palette.mode === 'light'
              ? theme.palette.grey['100']
              : theme.palette.grey['700'],
          '&:hover': {
            backgroundColor: theme.palette.background.default
          }
        }
      },
      {
        props: { position: 'absolute' },
        style: {
          position: 'absolute'
        }
      },
      {
        props: { variant: 'link' },
        style: {
          fontWeight: theme.typography.fontWeightRegular,
          color: theme.palette.primary.main,
          minWidth: 'auto',
          padding: 0,
          margin: 0,
          border: 'none',
          background: 'none',
          ':hover': {
            background: 'none',
            textDecoration: 'underline'
          }
        }
      }
    ]
  };

  // https://mui.com/api/form-control/#css
  theme.components.MuiFormControl = {
    styleOverrides: {
      root: {}
      // marginNormal: {
      //   marginTop: 16,
      //   marginBottom: 8
      // }
    }
  };

  theme.components.MuiFormLabel = {
    defaultProps: {},
    styleOverrides: {},
    variants: [
      {
        props: { variant: 'h5' },
        style: {
          color: theme.palette.text.primary,
          lineHeight: theme.spacing(2.5),
          fontSize: theme.mixins.pxToRem(15),
          fontWeight: 'bold'
        }
      }
    ]
  };

  // https://mui.com/api/input-label/#css
  theme.components.MuiInputLabel = {
    defaultProps: {},
    styleOverrides: {
      root: {},
      outlined: {
        paddingRight: theme.spacing(0.5),
        transform: 'translate(14px, 14px) scale(1)'
      },
      shrink: {
        '&.MuiInputLabel-outlined': {
          transform: 'translate(14px, -8px) scale(0.8) !important'
        },
        '&.MuiInputLabel-filled': {
          transform: 'translate(12px, 7px) scale(0.7)'
        },
        '&.MuiInputLabel-sizeSmall.MuiInputLabel-filled': {
          transform: 'translate(12px, -7px) scale(0.7) !important'
        }
      },
      sizeSmall: {
        '&.MuiInputLabel-outlined': {
          transform: 'translate(14px, 10px) scale(1) !important'
        },
        '&.MuiInputLabel-filled': {
          transform: 'translate(14px, 8px) scale(1) !important'
        }
      }
    }
  };

  // https://mui.com/api/outlined-input/#css
  theme.components.MuiOutlinedInput = {
    styleOverrides: {
      root: {
        borderRadius: 4,
        fontSize: '16px',
        '&.Mui-disabled .MuiOutlinedInput-notchedOutline': {
          ...(theme.palette.mode === 'dark' &&
            {
              // borderColor: 'rgba(255, 255, 255, 0.23)'
            })
        },
        '&.Mui-disabled .MuiSelect-iconOutlined': {
          ...(theme.palette.mode === 'dark' && {
            color: '#bdbdbd'
          })
        }
      },
      input: {
        fontSize: '15px',
        paddingTop: (48 - 15) / 2,
        paddingBottom: (48 - 15) / 2,
        height: '15px', // cap by line-height,
        '&.MuiSelect-select': {
          padding: '13px 15px'
        }
      },
      multiline: {
        // paddingTop: 0
      },
      inputSizeSmall: {
        fontSize: '13px',
        height: '13px',
        paddingTop: (40 - 13) / 2,
        paddingBottom: (40 - 13) / 2
      }
    }
  };

  // https://mui.com/api/filled-input/#css
  theme.components.MuiFilledInput = {
    defaultProps: {},
    styleOverrides: {
      root: {
        borderRadius: 4,
        fontSize: '16px'
      },
      input: {
        fontSize: '15px',
        paddingTop: 48 - 15 - 8,
        paddingBottom: 8,
        height: '15px' // cap by line-height,
      },
      inputSizeSmall: {
        fontSize: '13px',
        paddingTop: (40 - 13) / 2
      }
    }
  };

  // https://mui.com/api/input-base/#css
  theme.components.MuiInputBase = {
    styleOverrides: {
      root: {
        lineHeight: theme.mixins.pxToRem(22)
      },
      adornedStart: { paddingLeft: theme.spacing(2) },
      inputAdornedStart: { paddingLeft: theme.spacing(1) },
      adornedEnd: {},
      input: {
        WebkitAppearance: 'initial',
        '&::placeholder': {
          opacity: 0.5,
          color: theme.palette.text.primary
        }
      },
      inputSizeSmall: {}
    },
    variants: [
      {
        props: { variant: 'search' },
        style: {
          height: theme.spacing(5),
          borderRadius: theme.spacing(2.5),
          paddingRight: theme.spacing(1),
          backgroundColor: theme.palette.action.hover,
          transition: 'all 300ms cubic-bezier(0.4, 0, 0.2, 1) 0ms',
          border: theme.mixins.border('secondary'),
          '& .MuiIcon-root': {
            opacity: 0.43
          },
          '&.Mui-focused': {
            border: theme.mixins.border('primary'),
            borderColor: theme.palette.primary.main,
            backgroundColor: theme.palette.background.paper,
            '& .MuiIcon-root': {
              opacity: 1,
              color: theme.palette.primary.main
            }
          }
        }
      }
    ]
  };

  // https://mui.com/api/input/#css
  theme.components.MuiInput = {};

  // https://mui.com/api/icon-button/#css
  theme.components.MuiIconButton = {
    defaultProps: {
      size: 'small'
    },
    styleOverrides: {
      root: {
        ...(theme.palette.mode === 'dark' && {
          color: '#FFF'
        }),
        '&.liked': {
          color: theme.palette.primary.main,
          ...(theme.palette.mode === 'dark' && {
            borderColor: '#FFF'
          })
        },
        '&.joined': {
          color: theme.palette.primary.main,
          ...(theme.palette.mode === 'dark' && {
            borderColor: '#FFF'
          })
        },
        '&:disabled': {
          color: theme.palette.text.disabled,
          borderColor: theme.palette.text.disabled,
          cursor: 'not-allowed',
          pointerEvents: 'auto'
        },
        '&.Mui-disabled': {
          color: theme.palette.text.disabled,
          borderColor: theme.palette.text.disabled,
          cursor: 'not-allowed',
          pointerEvents: 'auto'
        }
      },
      sizeSmall: {
        width: 32,
        height: 32,
        fontSize: '18px'
      },
      sizeMedium: {
        width: 40,
        height: 40,
        fontSize: '20px'
      },
      sizeLarge: {
        width: 48,
        height: 48,
        fontSize: '24px'
      }
    },
    variants: [
      {
        props: { size: 'smaller' },
        style: {
          width: 28,
          height: 28,
          fontSize: '13px'
        }
      },
      {
        props: { size: 'smallest' },
        style: {
          width: 24,
          height: 24,
          fontSize: '13px'
        }
      },
      {
        props: { variant: 'outlined' },
        style: {
          border: '1px solid',
          borderColor: 'currentColor'
        }
      },
      {
        props: { variant: 'outlined-square' },
        style: {
          border: '1px solid',
          borderColor: 'currentColor',
          borderRadius: 4
        }
      },
      {
        props: { variant: 'contained-square' },
        style: {
          backgroundColor: theme.palette.primary.main,
          borderRadius: 4,
          color: 'white',
          '&:hover': {
            backgroundColor: theme.palette.primary.main
          }
        }
      },
      {
        props: { variant: 'white-contained' },
        style: {
          backgroundColor:
            theme.palette.mode === 'dark' ? theme.palette.grey[600] : 'white',
          '&:hover': {
            backgroundColor:
              theme.palette.mode === 'dark' ? theme.palette.grey[600] : 'white'
          }
        }
      },
      {
        props: { color: 'textSecondary' },
        style: { color: `${theme.palette.text.secondary} !important` }
      },
      {
        props: { variant: 'blacked' },
        style: {
          background: 'rgba(0,0,0,0.8)',
          color: 'rgba(255,255,255,0.9)',
          '&:hover': {
            background: 'rgba(0,0,0,0.9)',
            color: 'rgba(255,255,255,0.95)'
          }
        }
      }
    ]
  };

  // https://mui.com/api/list-item-icon/#css
  theme.components.MuiListItemIcon = {
    styleOverrides: {
      root: {
        minWidth: '24px !important',
        color: 'inherit'
      }
    }
  };

  // https://mui.com/api/form-helper-text/#css
  theme.components.MuiFormHelperText = {
    styleOverrides: {
      root: {
        fontSize: '13px',
        color: theme.palette.grey['500'],
        whiteSpace: 'pre-wrap',
        margin: 0,
        marginTop: '6px'
      }
    }
  };

  theme.components.MuiSkeleton = {
    variants: [
      {
        props: { variant: 'avatar' },
        style: { borderRadius: '50%' }
      },
      {
        props: { variant: 'button' },
        style: { borderRadius: 4 }
      },
      {
        props: { variant: 'icon-button' },
        style: { borderRadius: '50%' }
      }
    ]
  };
  // https://mui.com/api/switch/#css
  theme.components.MuiSwitch = {
    variants: [
      {
        props: { variant: 'ios' },
        style: {
          width: 33,
          height: 20,
          padding: 0,

          '&.MuiSwitch-sizeSmall': {
            width: 33,
            height: 20,
            padding: 3,
            '& .MuiSwitch-thumb': {
              width: 12,
              height: 12
            },
            '&.Mui-checked': {
              transform: 'translateX(9px) !important'
            }
          },
          '& .MuiSwitch-switchBase': {
            padding: 1,
            height: 20,
            width: 20,
            '&.Mui-checked': {
              transform: 'translateX(13px) !important'
            }
          },
          '& .MuiSwitch-thumb': {
            width: 18,
            height: 18,
            color: '#fff'
          },

          '& .MuiSwitch-track': {
            borderRadius: 10,
            backgroundColor: theme.palette.grey[300],
            opacity: '1 !important',
            '.Mui-disabled + &': {
              opacity: '0.2 !important'
            }
          }
        }
      }
    ]
  };

  // https://mui.com/api/select/#css
  theme.components.MuiSelect = {
    styleOverrides: {
      select: {
        '&.MuiInputBase-inputAdornedStart': {
          paddingLeft: 48
        },
        '&.MuiInputBase-inputSizeSmall:not(.MuiSelect-select)': {
          padding: '9px 8px'
        }
      }
    },
    variants: []
  };

  theme.components.MuiDialog = {
    styleOverrides: {
      paper: {
        backgroundImage: 'none',
        transition: 'max-height 1s ease-in-out'
      }
    },
    variants: [
      {
        props: { variant: 'alert' },
        style: {
          '& .MuiDialogContent-root': {
            paddingTop: `${16}px !important`
          }
        }
      }
    ]
  };

  // https://mui.com/api/dialog-title/#css
  theme.components.MuiDialogTitle = {
    styleOverrides: {
      root: {
        display: 'flex',
        alignItems: 'center',
        fontSize: '1.125rem',
        padding: theme.spacing(0, 2),
        fontWeight: theme.typography.fontWeightBold,
        position: 'relative',
        borderBottom: '1px solid',
        borderBottomColor: theme.palette.divider,
        minHeight: 56
      }
    }
  };

  // https://mui.com/api/dialog-content/#css
  theme.components.MuiDialogContent = {
    defaultProps: {
      dividers: true
    },
    styleOverrides: {
      root: {
        padding: theme.spacing(2)
      },
      dividers: {
        borderBottom: 'none',
        borderTop: 'none',
        '& + .MuiDialogActions-root': {
          borderTop: '1px solid',
          borderTopColor: theme.palette.divider,
          padding: theme.spacing(2, 0),
          margin: theme.spacing(2, 2, 0)
        },
        '& + .noSeparator': {
          borderTop: 'none',
          padding: theme.spacing(0, 0, 2, 0),
          margin: theme.spacing(0, 2)
        }
      }
    },
    variants: [
      {
        props: { variant: 'fit' },
        style: { padding: '0 !important' }
      },
      {
        props: { variant: 'fitScroll' },
        style: {
          maxHeight: 'unset',
          display: 'flex',
          flexDirection: 'column',
          padding: '0 !important'
        }
      }
    ]
  };

  theme.components.MuiDialogActions = {
    styleOverrides: {
      root: {
        flexDirection: 'row-reverse',
        justifyContent: 'flex-start',
        padding: theme.spacing(2),
        '& >div+div': {
          marginRight: theme.spacing(1)
        },
        '& >button+button': {
          marginRight: theme.spacing(1)
        }
      }
    }
  };

  theme.components.MUIRichTextEditor = {
    styleOverrides: {
      placeHolder: {
        color: theme.palette.text.secondary
      }
    }
  };
}
