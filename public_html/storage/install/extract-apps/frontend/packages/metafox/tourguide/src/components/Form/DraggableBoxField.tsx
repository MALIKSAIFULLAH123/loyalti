/**
 * @type: formElement
 * name: form.element.DraggableBox
 * chunkName: formExtras
 */

import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { Box, FormControl, styled, Tooltip, Typography } from '@mui/material';
import { camelCase, isEmpty } from 'lodash';
import loadable from '@loadable/component';
import { DraggableData } from 'react-draggable';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  calculatorPosition2Persent,
  calculatePositionWithBoundsRotate,
  calculatorPersent2Position,
  mappingRotate
} from '@metafox/tourguide/utils/drag';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const widthContent = 165;
const heightContent = 45;

const name = 'TourGuideDraggableBoxField';

const DragContainer = styled(Box, {
  name,
  slot: 'DragContainer'
})(({ theme }) => ({
  display: 'block',
  position: 'relative',
  width: '100%',
  background: '#ccc',
  overflow: 'hidden'
}));

const ContentBox = styled(Box, {
  name,
  slot: 'ContentBox'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  position: 'absolute',
  left: 0,
  top: 0,
  bottom: 0,
  right: 0,
  boxShadow: theme.shadows[1],
  background: theme.palette.primary.main,
  color: theme.palette.primary.contrastText,
  borderRadius: theme.shape.borderRadius * 3,
  cursor: 'move'
}));

const WrapperContent = styled(Box, {
  name,
  slot: 'WrapperContent'
})(({ theme }) => ({
  padding: theme.spacing(1.5),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between'
}));

const RotateBtn = styled('div', {
  name,
  slot: 'RotateBtn'
})(({ theme }) => ({
  width: 24,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

const ItemContent = styled(Box, { name, slot: 'ItemContent' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const IconItem = styled(Box, {
  name,
  slot: 'IconItem'
})(({ theme }) => ({
  width: 24,
  height: 24,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  marginRight: theme.spacing(1),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

const RotateWrapper = styled(Box, { name, slot: 'RotateWrapper' })(() => ({
  width: widthContent,
  height: heightContent,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const DraggableBoxField = ({ name, config, disabled }: FormFieldProps) => {
  const { i18n } = useGlobal();

  const {
    variant,
    margin,
    fullWidth,
    sxFieldWrapper,
    sxDragContainer,
    sxDragContent,
    background,
    height,
    title = 'tourguide_start_tour'
  } = config;
  const [field, , { setValue }] = useField(name ?? 'DraggableBoxField');

  const [position, setPosition] = React.useState({ x: 0, y: 0 });
  const [rotation, setRotation] = React.useState(0);
  const dragContainerRef = React.useRef<HTMLDivElement>();
  const contentRef = React.useRef<HTMLDivElement>();
  const firstUpdate = React.useRef(true);
  const preventFirstUpdate = React.useRef(true);
  const prevRotateRef = React.useRef(field?.value?.rotation || 0);

  const offset = (heightContent - widthContent) / 2;

  const handleDrag = (e: Event, data: DraggableData) => {
    setPosition(data);
    const containerRect = dragContainerRef?.current?.getBoundingClientRect();
    const contentRect = contentRef?.current?.getBoundingClientRect();

    const calculatorPosition = calculatorPosition2Persent(
      data,
      containerRect,
      contentRect,
      rotation,
      offset
    );

    setValue({ position: calculatorPosition, rotation });
  };

  const boundTextDrag = React.useMemo(() => {
    const containerRect = dragContainerRef.current?.getBoundingClientRect();

    if (!containerRect) return 'parent';

    if (rotation % 180 === 0) {
      return {
        top: 0,
        left: 0,
        right: containerRect.width - widthContent,
        bottom: containerRect.height - heightContent
      };
    } else {
      return {
        top: 0 - offset,
        left: 0 + offset,
        right: containerRect.width - heightContent + offset,
        bottom: containerRect.height - widthContent - offset
      };
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dragContainerRef.current, contentRef.current, rotation]);

  const handleRotate = () => {
    prevRotateRef.current = rotation;
    const rotate = (rotation + 90) % 360;
    const containerRect = dragContainerRef.current?.getBoundingClientRect();

    if (!containerRect) return;

    const newPosition = calculatePositionWithBoundsRotate(
      position,
      containerRect,
      rotate,
      offset,
      { widthContent, heightContent }
    );

    setRotation(rotate);
    setPosition(newPosition);
  };

  React.useEffect(() => {
    if (preventFirstUpdate.current) {
      preventFirstUpdate.current = false;

      return;
    }

    if (prevRotateRef.current === rotation) return;

    const containerRect = dragContainerRef.current?.getBoundingClientRect();
    const contentRect = contentRef?.current?.getBoundingClientRect();

    const calculatorPosition = calculatorPosition2Persent(
      position,
      containerRect,
      contentRect,
      rotation,
      offset
    );

    setValue({ position: calculatorPosition, rotation });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rotation, position]);

  React.useEffect(() => {
    if (!firstUpdate.current) return;

    firstUpdate.current = false;

    if (isEmpty(field?.value)) {
      setPosition({ x: 0, y: 0 });

      return;
    }

    if (!dragContainerRef?.current) return;

    const handleSetPosition = () => {
      const containerRect = dragContainerRef?.current?.getBoundingClientRect();
      const rotate = field?.value?.rotation || 0;

      const position = calculatorPersent2Position(
        field?.value?.position,
        {
          width: containerRect?.width,
          height: containerRect?.height
        },
        { widthContent, heightContent },
        rotate,
        offset
      );

      setPosition(position);
      setRotation(rotate);
    };

    const resizeObserver = new ResizeObserver(() => {
      handleSetPosition();
    });

    handleSetPosition();
    resizeObserver.observe(dragContainerRef?.current);

    return () => resizeObserver.disconnect(); // clean up
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [field?.value]);

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      id={name}
      sx={sxFieldWrapper}
    >
      <DragContainer
        height={height}
        bgcolor={background}
        sx={sxDragContainer}
        ref={dragContainerRef}
      >
        <Draggable
          key={`Draggable_${rotation}`}
          disabled={disabled}
          bounds={boundTextDrag}
          position={position}
          onDrag={handleDrag}
          positionOffset={
            rotation % 180 !== 0
              ? {
                  x: 0 - offset,
                  y: offset
                }
              : undefined
          }
        >
          <RotateWrapper
            sx={{
              width: rotation % 180 === 0 ? widthContent : heightContent,
              height: rotation % 180 === 0 ? heightContent : widthContent
            }}
          >
            <ContentBox
              sx={sxDragContent}
              width={widthContent}
              height={heightContent}
              ref={contentRef}
              style={{
                transform: mappingRotate[rotation],
                transformOrigin: '0 0'
              }}
            >
              <WrapperContent>
                <ItemContent>
                  <IconItem>
                    <LineIcon icon="ico-tourguide" />
                  </IconItem>
                  <Typography variant="subtitle2" fontWeight={400}>
                    {i18n.formatMessage({
                      id: title ? title : 'tourguide_start_tour'
                    })}
                  </Typography>
                </ItemContent>
                <Tooltip title={i18n.formatMessage({ id: 'rotate' })}>
                  <RotateBtn onClick={handleRotate}>
                    <LineIcon icon="ico-rotate-right-alt" />
                  </RotateBtn>
                </Tooltip>
              </WrapperContent>
            </ContentBox>
          </RotateWrapper>
        </Draggable>
      </DragContainer>
    </FormControl>
  );
};

export default DraggableBoxField;
