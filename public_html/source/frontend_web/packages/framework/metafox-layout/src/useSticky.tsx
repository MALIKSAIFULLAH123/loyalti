/**
 * @type: service
 * name: useSticky
 */
import React, { useLayoutEffect, useState, RefObject } from 'react';

export type DimensionProps = {
  top?: number;
};

type HookProps = {
  elRef: RefObject<HTMLElement | null>;
} & DimensionProps;

type Position = React.CSSProperties['position'];

const ceil = (num: number) => Math.ceil(num);

const useStickyElement = ({
  elRef,
  top: topProp
}: HookProps): {
  top: number;
  position: Position;
} => {
  const [top, setTop] = useState(0);
  const [position, setPosition] = useState<Position>('relative');

  useLayoutEffect(() => {
    const element = elRef.current;
    let prevScrollTop = window.scrollY;

    if (!element) return;

    const offset = ceil(element.offsetTop);
    const topPositionAtWhichElementBecomesStickyFromTop =
      topProp ?? ceil(element.getBoundingClientRect()?.top - window.scrollY) ?? 0;

    const handleScroll = () => {
      const elementBoundingRect = element.getBoundingClientRect();
      const elementBoundingRectTop = ceil(elementBoundingRect.top);
      const elementBoundingRectBottom = ceil(elementBoundingRect.bottom);
      const topDistanceOfElementRelativeToPageTop = ceil(element.offsetTop);
      const topDistanceAtWhichElementBecomesStickyFromBottom =
        elementBoundingRectTop;

      const scrollYOffset = window.scrollY;
      const isScrollingUp = scrollYOffset < prevScrollTop;
      const isScrollingDown = scrollYOffset > prevScrollTop;
      const windowHeight = document.documentElement.clientHeight;
      const windowOffsetHeight = document.body.offsetHeight;

      const topEndPosition =
        elementBoundingRectTop - topPositionAtWhichElementBecomesStickyFromTop;
      const bottomEndPosition = elementBoundingRectBottom;

      const threshold = Math.max(windowHeight / 2, 100);

      // check when sidebar component that determines height
      if (windowOffsetHeight - threshold < elementBoundingRect.height) return;

      const isTopEndAboveViewport = topEndPosition < 0;
      const isTopEndBelowViewport = topEndPosition > windowHeight;
      const isBottomEndBelowViewport = bottomEndPosition > windowHeight;
      const isBottomEndAboveViewport = bottomEndPosition < 0;

      const isTopEndBetweenViewport =
        !isTopEndAboveViewport && !isTopEndBelowViewport;
      const isBottomEndBetweenViewport =
        !isBottomEndAboveViewport && !isBottomEndBelowViewport;

      const areBothTopAndBottomEndsOnOppositeEndsOfViewport =
        isTopEndAboveViewport && isBottomEndBelowViewport;
      const areBothTopAndBottomEndsBetweenViewport =
        isTopEndBetweenViewport && isBottomEndBetweenViewport;

      if (isTopEndBelowViewport || isBottomEndAboveViewport) {
        setPosition('relative');
        setTop(scrollYOffset);
        prevScrollTop = scrollYOffset;

        return;
      }

      if (areBothTopAndBottomEndsOnOppositeEndsOfViewport) {
        setPosition('relative');
        setTop(topDistanceOfElementRelativeToPageTop - offset);
        prevScrollTop = scrollYOffset;

        return;
      }

      if (areBothTopAndBottomEndsBetweenViewport) {
        setPosition('sticky');
        setTop(topPositionAtWhichElementBecomesStickyFromTop);
        prevScrollTop = scrollYOffset;

        return;
      }

      if (isScrollingUp) {
        if (isTopEndBetweenViewport) {
          setPosition('sticky');
          setTop(topPositionAtWhichElementBecomesStickyFromTop);
        } else if (isBottomEndBetweenViewport) {
          setPosition('relative');
          setTop(topDistanceOfElementRelativeToPageTop - offset);
        }
      } else if (isScrollingDown) {
        if (isTopEndBetweenViewport) {
          setPosition('relative');
          setTop(topDistanceOfElementRelativeToPageTop - offset);
        } else if (isBottomEndBetweenViewport) {
          setPosition('sticky');
          setTop(topDistanceAtWhichElementBecomesStickyFromBottom);
        }
      }

      prevScrollTop = scrollYOffset;
    };
    window.addEventListener('scroll', handleScroll, { passive: true });

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);

  return { top, position };
};

export default useStickyElement;
