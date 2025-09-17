/**
 * @type: ui
 * name: layout.liveStickySlot
 * chunkName: boot
 */
import { useGlobal } from '@metafox/framework';
import { camelCase } from 'lodash';
import React from 'react';
import { LayoutSlotProps } from '@metafox/layout/types';
import {
  Slot,
  SlotContent,
  SlotStage
} from '@metafox/layout/LayoutSlot/StyledSlot';
import { Box, styled } from '@mui/material';

const StickyFooter = styled(Box, {
  name: 'LayoutSlot',
  slot: 'StickyFooter',
  overridesResolver: (props, styles) => [styles.stickyFooter]
})({
  height: 88
});

// Support Drag & Drop
export default function LayoutStickySlot(props: LayoutSlotProps) {
  const { jsxBackend, useLayout, useSticky } = useGlobal();
  const { blocks } = useLayout();
  const {
    slotName,
    showEmpty = true,
    elements,
    flexWeight,
    xs,
    rootStyle,
    stageStyle,
    contentStyle
  } = props;

  const [clientHeight, setClientHeight] = React.useState<number>(0);
  const sideRef = React.useRef(null);

  const { top: _top, position } = useSticky({
    elRef: sideRef
  });

  // Start observing the element when the component is mounted
  React.useEffect(() => {
    const element = sideRef?.current;

    if (!element) return;

    const observer = new ResizeObserver(entries => {
      // 👉 Do something when the element is resized
      setClientHeight(entries[0]['contentRect'].height);
    });

    observer.observe(element);

    return () => {
      // Cleanup the observer by unobserving all elements
      observer.disconnect();
    };
  }, []);

  const items =
    elements ??
    blocks
      .filter(item => item.slotName === slotName)
      .sort((a, b) => a.order - b.order);

  if (!items.length && !showEmpty) {
    return null;
  }

  const useFlex = '0' < flexWeight || !xs;

  return (
    <Slot
      name={slotName}
      item
      xs={useFlex ? undefined : xs}
      useFlex={useFlex}
      flexWeight={flexWeight}
      data-testid={camelCase(`LayoutSlot_${slotName}`)}
      minHeight={clientHeight as any}
      {...rootStyle}
    >
      <SlotStage
        name={slotName}
        ref={sideRef}
        style={{
          top: _top,
          position,
          height: 'fit-content'
        }}
        {...stageStyle}
      >
        <SlotContent name={slotName} {...contentStyle}>
          {/* pseudo block for calculate useSticky */}
          <Box sx={{ height: '0.1px' }}></Box>
          {jsxBackend.render(items)}
        </SlotContent>
        <StickyFooter />
      </SlotStage>
    </Slot>
  );
}
