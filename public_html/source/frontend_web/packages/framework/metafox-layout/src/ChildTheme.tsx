import ThemeProvider from './ThemeProvider';
import React from 'react';

export default function ChildTheme({ themeName, children }) {
  if (themeName && themeName !== 'none') {
    return <ThemeProvider themeName={themeName}>{children}</ThemeProvider>;
  }

  return children;
}
