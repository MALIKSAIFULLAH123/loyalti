import { Box } from '@mui/material';
import React from 'react';

type Props = {
  children: React.ReactNode;
  minHeight?: number;
};

function GridMain({ children, minHeight }: Props) {
  const ref = React.useRef();
  const [className, setClassName] = React.useState('');

  React.useEffect(() => {
    if (!ref.current) return;

    const resizeObserver = new ResizeObserver(() => {
      const scrollWidth = ref?.current?.scrollWidth;
      const clientWidth = ref?.current?.clientWidth;
      setClassName(scrollWidth > clientWidth ? 'dataGrid-scroll' : '');
    });
    resizeObserver.observe(ref.current);

    return () => resizeObserver.disconnect(); // clean up
  }, []);

  return (
    <Box
      sx={{ position: 'relative', overflowX: 'auto', width: '100%', minHeight }}
      component="div"
      ref={ref}
      className={className}
      data-testid="MainGrid"
    >
      {children}
    </Box>
  );
}

export default GridMain;
