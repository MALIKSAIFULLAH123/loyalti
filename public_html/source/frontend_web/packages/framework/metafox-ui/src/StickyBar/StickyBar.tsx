import { useIsMobile } from '@metafox/framework';
import React, { useEffect, useRef, useState } from 'react';
import { styled } from '@mui/material';

interface State {
  isSticky: boolean;
  styledPseudoSticky?: React.CSSProperties;
  styledIsSticky?: React.CSSProperties;
}

const Root = styled('div', {
  name: 'StickyBar',
  slot: 'Root',
  overridesResolver(props, styles) {
    return [styles.root];
  }
})(({ theme }) => ({}));

const Stage = styled('div', {
  name: 'StickyBar',
  slot: 'Stage',
  shouldForwardProp: props => props !== 'sticky',
  overridesResolver(props, styles) {
    return [styles.stage, props.sticky && styles.stickyStage];
  }
})<{ sticky?: boolean }>(({ theme, sticky }) => ({
  ...(sticky && {
    transition: 'top .5s',
    backgroundColor: theme.mixins.backgroundColor('paper'),
    borderBottom: 'solid 1px',
    borderBottomColor: theme.palette.border?.secondary,
    borderRadius: 0,
    position: 'fixed',
    top: theme.appBarHeight?.fixed - 1,
    zIndex: 1000
  })
}));

export default function StickyBar(props: any) {
  const { children, className, sx } = props;
  const isMobile = useIsMobile();

  const [sticky, setSticky] = useState<State>({
    isSticky: false
  });
  const outerRef = useRef<HTMLDivElement>();
  const innerRef = useRef<HTMLDivElement>();
  const pseudoRef = useRef<HTMLDivElement>();

  useEffect(() => {
    if (!outerRef.current || !pseudoRef.current) return;

    const elementPosition: number = pseudoRef.current.offsetTop;
    const elementHeight: number = innerRef.current.offsetHeight;

    let lastSticky: boolean = false;

    const onScroll = () => {
      if (!elementPosition || !elementHeight) return;

      const winScroll = window.pageYOffset;

      if (elementPosition + 60 < winScroll) {
        const elementWrapperRect = outerRef.current.getBoundingClientRect();

        const elementWrapperPositionLeft = elementWrapperRect.left;
        const elementWrapperPositionRight =
          document.body.clientWidth - elementWrapperRect.right;

        if (!lastSticky) {
          lastSticky = true;
          // sticky
          setSticky({
            isSticky: true,
            styledPseudoSticky: { height: elementHeight },
            styledIsSticky: {
              left: isMobile ? 0 : elementWrapperPositionLeft,
              right: isMobile ? 0 : elementWrapperPositionRight
            }
          });
        }
      } else if (lastSticky) {
        lastSticky = false;
        setSticky({
          isSticky: false,
          styledPseudoSticky: { height: 0 },
          styledIsSticky: undefined
        });
      }
    };

    window.addEventListener('scroll', onScroll);

    return () => {
      window.removeEventListener('scroll', onScroll);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Root ref={outerRef}>
      <div
        data-pseudo="pseudo-sticky"
        ref={pseudoRef}
        style={sticky.styledPseudoSticky}
      />
      <Stage
        ref={innerRef}
        sticky={sticky.isSticky}
        className={className}
        style={sticky.styledIsSticky}
        sx={sx}
      >
        {children({ sticky: sticky.isSticky })}
      </Stage>
    </Root>
  );
}
