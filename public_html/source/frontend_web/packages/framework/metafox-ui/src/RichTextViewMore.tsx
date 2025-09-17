import { useGlobal } from '@metafox/framework';
import { styled, Box } from '@mui/material';
import { alpha } from '@mui/system/colorManipulator';
import React from 'react';
import { isFunction } from 'lodash';

const ToggleViewMoreBtn = styled('span', {
  name: 'ToggleViewMoreBtn',
  slot: 'Root'
})(({ theme }) => ({
  color: theme.palette.primary.main,
  marginTop: theme.spacing(1),
  fontWeight: theme.typography.fontWeightBold,
  display: 'inline-flex',
  fontSize: theme.mixins.pxToRem(13),
  lineHeight: 1,
  ':hover': {
    textDecoration: 'underline'
  }
}));

const Wrapper = styled(Box, {
  name: 'Wrapper',
  slot: 'Root'
})(({ theme }) => ({
  display: 'block',
  overflow: 'hidden',
  position: 'relative'
}));
const ShadowContent = styled('div', {
  name: 'ShadowContent',
  slot: 'Root'
})(({ theme }) => ({
  display: 'flex',
  height: '58px',
  background: `linear-gradient(to top, ${
    theme.palette.background.paper
  } 30%, ${alpha(theme.palette.background.paper, 0.8)} 70%, ${alpha(
    theme.palette.background.paper,
    0.9
  )} 100%)`,
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0
}));

type RichTextViewMoreProps = {
  component?: React.ElementType;
  children?: React.ReactNode;
  textViewMore?: string;
  textViewLess?: string;
  defaultShowFull?: boolean;
  maxHeight?: string;
};

const RichTextViewMore = (props: RichTextViewMoreProps) => {
  const {
    component: AsComponent = 'div',
    children,
    textViewMore = 'view_more',
    textViewLess = 'view_less',
    defaultShowFull = false,
    maxHeight = '500px'
  } = props;

  const { i18n } = useGlobal();
  const [isFull, setIsFull] = React.useState(defaultShowFull);
  const [enable, setEnable] = React.useState(false);
  const ref = React.useRef<HTMLDivElement>();
  const refResize = React.useRef<ResizeObserver>();
  const checkEnable = React.useCallback(() => {
    if (ref.current && ref.current.scrollHeight > ref.current.clientHeight) {
      setEnable(true);
    }
  }, [ref?.current]);

  React.useEffect(() => {
    if (!ref.current) return;

    // need listener observer because div height will change when load image
    refResize.current = new ResizeObserver(() => {
      // Do what you want to do when the size of the element changes
      checkEnable();
    });

    refResize.current?.observe(ref.current);

    return () => refResize?.current.disconnect(); // clean up
  }, []);

  const handleClickMore = () => {
    if (refResize.current && isFunction(refResize?.current.disconnect)) {
      refResize?.current.disconnect();
    }

    setIsFull(prev => !prev);
  };

  if (!children) return null;

  const heightLimit = isFull ? 'auto' : maxHeight;

  return (
    <AsComponent>
      <Wrapper ref={ref} sx={{ maxHeight: heightLimit }}>
        {children}
        {enable && !isFull ? <ShadowContent /> : null}
      </Wrapper>
      {enable ? (
        <ToggleViewMoreBtn onClick={handleClickMore} role="button">
          {i18n.formatMessage({ id: isFull ? textViewLess : textViewMore })}
        </ToggleViewMoreBtn>
      ) : null}
    </AsComponent>
  );
};

export default RichTextViewMore;
