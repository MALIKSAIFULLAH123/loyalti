import * as React from 'react';
import MuiTooltip from '@mui/material/Tooltip';

export default function Tooltip(props) {
  const { children: child, ...rest } = props || {};
  const [show, setShow] = React.useState(false);

  const hideTooltip = React.useCallback(() => {
    setShow(false);
  }, [setShow]);

  const enhancedChildren = () => {
    if (React.isValidElement(child)) {
      return React.cloneElement(child, {
        onClick: (event: React.MouseEvent) => {
          // Custom hide tooltip
          hideTooltip();

          // Call the original onClick if it exists
          if (child.props.onClick) {
            child.props.onClick(event);
          }
        }
      });
    }

    return child;
  };

  return (
    <MuiTooltip
      open={show}
      disableHoverListener
      onMouseEnter={() => setShow(true)}
      onMouseLeave={() => setShow(false)}
      {...rest}
    >
      {enhancedChildren()}
    </MuiTooltip>
  );
}
