import { RefOf } from '@metafox/framework';
import { ScrollbarProps, Scrollbars } from '@metafox/scrollbars';
import React from 'react';
import ScrollProvider from './ScrollProvider';

export type ScrollContainerProps = Omit<ScrollbarProps, 'scrollRef'>;

function ScrollContainer(
  {
    children,
    autoHide = true,
    autoHeight = true,
    autoHeightMax,
    ...rest
  }: ScrollContainerProps,
  ref: RefOf<HTMLDivElement>
) {
  const scrollRef: any = React.useRef<HTMLDivElement>();
  const maxHeight =
    autoHeightMax ??
    (window.innerHeight ? Math.round(window.innerHeight * 0.5) : 300);

  return (
    <Scrollbars
      autoHide={autoHide}
      autoHeight={autoHeight}
      autoHeightMax={maxHeight}
      scrollRef={ref ?? scrollRef}
      {...rest}
    >
      <ScrollProvider scrollRef={ref ?? scrollRef}>{children}</ScrollProvider>
    </Scrollbars>
  );
}

export default React.forwardRef(ScrollContainer);
