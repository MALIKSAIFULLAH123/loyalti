import { RefOf } from '@metafox/framework';
import { LineIconProps } from '@metafox/ui';
import { styled } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import './lineicon.css';

const regexPattern = /^ico-/;

const StyledIcon = styled('span', {
  name: 'MuiIcon',
  slot: 'root',
  skipSx: false,
  overridesResolver: (props, styles) => [styles.root]
})({
  fontFamily: "'lineficon' !important",
  speak: 'none',
  fontStyle: 'normal',
  fontWeight: 'normal',
  fontVariant: 'normal',
  textTransform: 'none',
  fontSmoothing: 'antialiased',
  WebkitFontSmoothing: 'antialiased'
  // lineHeight: 'inherit'
});

export default React.forwardRef(
  (
    {
      component: AsComponent = 'span',
      icon = 'ico-repeat-alt',
      className,
      ...props
    }: LineIconProps,
    ref: RefOf<HTMLSpanElement>
  ) => {
    // support with prefix Mobile App icon (old data)
    const iconExtra =
      icon && regexPattern.test(icon) ? undefined : `ico-${icon}`;

    return (
      <StyledIcon
        ref={ref}
        className={clsx(className, 'ico', icon, iconExtra)}
        {...props}
      />
    );
  }
);
