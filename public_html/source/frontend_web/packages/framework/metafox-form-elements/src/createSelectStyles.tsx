import { Theme } from '@mui/material';
import { CSSProperties as Css } from 'react';

export default function createSelectStyles(
  theme: Theme,
  variant: 'outlined' | 'filled' | 'standard',
  size: 'small' | 'medium'
) {
  return {
    control(provided: Css, state): Css {
      const borderColor = state.isFocused
        ? theme.palette.primary.main
        : '#0000003b';
      const borderWidth = state.isFocused ? '2px' : '1px';
      const height = size === 'small' ? '40px' : '48px';

      switch (variant) {
        case 'outlined':
          return {
            ...provided,
            height,
            borderColor: `${borderColor} !important`,
            borderWidth,
            boxSizing: 'border-box',
            outline: 0,
            boxShadow: 'none',
            borderRadius: 4,
            animationName: 'mui-auto-fill-cancel',
            padding:
              size === 'medium'
                ? theme.spacing(0.5, 0, 0.5, 1.5)
                : theme.spacing(0.5, 0, 0.5, 1)
          };
        case 'standard':
          return {
            ...provided,
            outline: 0,
            height,
            borderRadius: 0,
            boxSizing: 'border-box',
            boxShadow: 'none',
            borderWidth: state.isFocused ? '0 0 2px 0' : '0 0 1px 0',
            padding: size === 'medium' ? theme.spacing(2, 0, 0, 0) : 0,
            borderColor
          };
      }

      return provided;
    },
    valueContainer(provided: Css): Css {
      return { ...provided, padding: 0 };
    },
    input(provided: Css): Css {
      return { ...provided, padding: 0, margin: 0 };
    },
    placeholder(provided: Css): Css {
      return { ...provided, padding: 0, margin: 0 };
    },
    menu(provided: Css): Css {
      return { ...provided, zIndex: 2, marginTop: '1px' };
    },
    indicatorContainer(provided: Css): Css {
      return {
        ...provided,
        padding: '8px 4px 8px',
        color: theme.palette.text.primary
      };
    },
    groupHeading(provided: Css): Css {
      return provided;
    },
    menuList(provided: Css): Css {
      return { ...provided, maxHeight: '30vh' };
    }
  };
}
