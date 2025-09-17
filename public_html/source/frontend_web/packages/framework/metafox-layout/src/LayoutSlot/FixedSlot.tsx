/**
 * @type: ui
 * name: layout.liveFixedSlot
 * chunkName: boot
 */
import { useFixedRect, useGlobal } from '@metafox/framework';
import { Scrollbars } from '@metafox/scrollbars';
import { Divider } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import ThemeWrapper from '../ChildTheme';
import ScrollProvider from '../ScrollProvider';
import { LayoutSlotProps } from '../types';
import {
  FixedContent,
  FixedFooter,
  FixedHeader,
  FixedStage,
  Slot,
  SlotContent,
  SlotStage
} from './StyledSlot';

export default function FixedSlotContainer(props: LayoutSlotProps) {
  const wrapper = React.useRef<HTMLDivElement>();
  const { jsxBackend, useLayout, usePrevious } = useGlobal();
  const { blocks } = useLayout();
  const fixedRect = useFixedRect(wrapper);
  const scrollRef = React.useRef();
  const [hasDivider, setHasDivider] = React.useState<boolean>(false);

  const {
    slotName,
    showEmpty = true,
    flexWeight,
    xs,
    layoutEditMode,
    elements,
    disabledScroll,
    contentStyle,
    rootStyle,
    stageStyle,
    themeName
  } = props;
  const items =
    elements ??
    blocks
      .filter(item => item.slotName === slotName)
      .sort((a, b) => a.order - b.order);

  const prevIdComponents = usePrevious(
    items?.map(item => item?.component).join('')
  );

  if (!items.length && !showEmpty) {
    return null;
  }

  const isLiveEdit = layoutEditMode & 1;
  const enableFix = 767 < window.outerWidth;
  const hasFreeze = items.filter(c => c.props.freeze).length > 0;
  const hasFreezeBottom = items.filter(c => c.props.freezeBottom).length > 0;

  const useFlex = '0' < flexWeight || !xs;

  // eslint-disable-next-line react-hooks/rules-of-hooks
  React.useEffect(() => {
    if (!hasFreeze || !scrollRef.current) return;

    const ele: HTMLDivElement = scrollRef.current;
    const initialScrollRefPosition: number = ele.getBoundingClientRect().top;

    let isSetBorder: boolean = false;

    const onScroll = () => {
      const currentScrollRefPosition: number =
        ele.scrollTop + initialScrollRefPosition;

      if (currentScrollRefPosition > initialScrollRefPosition) {
        if (!isSetBorder) {
          isSetBorder = true;
          setHasDivider(true);
        }
      } else if (isSetBorder) {
        isSetBorder = false;
        setHasDivider(false);
      }
    };

    ele.addEventListener('scroll', onScroll);

    return () => {
      ele.removeEventListener('scroll', onScroll);
    };
  }, [hasFreeze]);

  // eslint-disable-next-line react-hooks/rules-of-hooks
  React.useEffect(() => {
    const ele: HTMLDivElement = scrollRef.current;

    if (!ele) return;

    ele.scrollTop = 0;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [prevIdComponents]);

  return (
    <ThemeWrapper themeName={themeName}>
      <Slot
        name={slotName}
        item
        {...rootStyle}
        flexWeight={flexWeight}
        useFlex={useFlex}
        xs={useFlex ? undefined : xs}
        data-testid={camelCase(`LayoutSlot_${slotName}`)}
      >
        <SlotStage
          name={slotName}
          liveEdit={Boolean(isLiveEdit)}
          {...stageStyle}
        >
          <div ref={wrapper}>
            <SlotContent
              name={slotName}
              style={fixedRect.style}
              fixed={Boolean(enableFix && fixedRect.width)}
              {...contentStyle}
            >
              {hasFreeze ? (
                <FixedStage>
                  <FixedHeader>
                    {jsxBackend.render(items, c => c.props.freeze)}
                  </FixedHeader>
                  {hasDivider ? <Divider variant={'middle'} /> : null}
                  <FixedContent>
                    {disabledScroll ? (
                      jsxBackend.render(items)
                    ) : (
                      <ScrollProvider scrollRef={scrollRef}>
                        <Scrollbars autoHide scrollRef={scrollRef}>
                          {jsxBackend.render(
                            items,
                            c => !c.props.freeze && !c.props.freezeBottom
                          )}
                        </Scrollbars>
                      </ScrollProvider>
                    )}
                  </FixedContent>
                  {hasFreezeBottom ? (
                    <FixedFooter>
                      {jsxBackend.render(items, c => c.props.freezeBottom)}
                    </FixedFooter>
                  ) : null}
                </FixedStage>
              ) : disabledScroll ? (
                jsxBackend.render(items)
              ) : (
                <ScrollProvider scrollRef={scrollRef}>
                  <Scrollbars autoHide scrollRef={scrollRef}>
                    {jsxBackend.render(items)}
                  </Scrollbars>
                </ScrollProvider>
              )}
            </SlotContent>
          </div>
        </SlotStage>
      </Slot>
    </ThemeWrapper>
  );
}
