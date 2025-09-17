import { Button, IconButton, SxProps } from '@mui/material';
import { Theme } from '@mui/material/styles';
import { debounce } from 'lodash';
import React from 'react';

type Props = {
  size?: 'small' | 'medium' | 'large' | 'smaller' | 'smallest';
  action?: any;
  sx?: SxProps<Theme>;
  variant?: string;
  children: any;
  isIcon?: boolean;
  color?: string;
  disabled?: boolean;
  className?: string;
  autoEnable?: boolean;
};

const DELAY = 2000;

export default function ButtonAction({
  size,
  action,
  sx,
  variant,
  children,
  isIcon = false,
  color,
  disabled: forceDisabled,
  className,
  autoEnable,
  ...rest
}: Props) {
  const [disabled, setDisabled] = React.useState(false);

  const enableButton = React.useCallback(() => {
    setDisabled(false);
  }, []);

  const debounceTrigger = React.useMemo(
    () => debounce(enableButton, DELAY),
    [enableButton]
  );

  const onClick = () => {
    setDisabled(true);
    action(() => setDisabled(false));

    // support some special case can't sure handle enable again button
    if (autoEnable) {
      debounceTrigger();
    }
  };

  if (isIcon)
    return (
      <IconButton
        {...rest}
        disabled={disabled || forceDisabled}
        size={size}
        onClick={onClick}
        sx={sx}
        variant={variant}
        color={color}
        className={className}
      >
        {children}
      </IconButton>
    );

  return (
    <Button
      {...rest}
      disabled={disabled || forceDisabled}
      size={size}
      onClick={onClick}
      sx={sx}
      variant={variant}
      color={color}
      className={className}
    >
      {children}
    </Button>
  );
}
