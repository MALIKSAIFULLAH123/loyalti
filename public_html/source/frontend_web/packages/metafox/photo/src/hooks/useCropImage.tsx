/* eslint-disable */
import React, { useCallback } from 'react';

type OrdShape = 's' | 'n' | 'w' | 'e' | 'sw' | 'ne' | 'nw' | 'se';
type CropUnit = 'px' | '%';

type CropShape = {
  aspect: number;
  x: number;
  y: number;
  width: number;
  height: number;
  unit: CropUnit;
};

type CropSelectionConfig = {
  imageContainerRef: React.MutableRefObject<HTMLDivElement>;
  imageWrapperRef: React.MutableRefObject<HTMLDivElement>;
  cropSelectionRef: React.MutableRefObject<HTMLDivElement>;
  imageRef: React.MutableRefObject<HTMLImageElement>;
  onComplete?: Function;
  onChange?: Function;
  onDragStart?: Function;
  onDragEnd?: Function;
  crop?: CropShape;
  minWidth?: number;
  maxWidth?: number;
  minHeight?: number;
  maxHeight?: number;
  disabled?: boolean;
  locked?: boolean;
  keepSelection?: boolean;
};

type EvData = Partial<{
  ord: OrdShape;
  clientStartX: number;
  clientStartY: number;
  cropStartWidth: number;
  cropStartHeight: number;
  xDiff: number;
  yDiff: number;
  xCrossOver: any;
  yCrossOver: any;
  xDiffPc: number;
  yDiffPc: number;
  cropStartY: number;
  cropStartX: number;
  xInversed: any;
  yInversed: any;
  lastYCrossover: any;
  lastXCrossover: any;
  cropOffset: any;
  startXCrossOver: any;
  startYCrossOver: any;
  inversedXOrd: any;
  inversedYOrd: any;
  isResize: boolean;
}>;

function getClientPos(e: any): { x: number; y: number } {
  let pageX: number;
  let pageY: number;

  if (e.touches) {
    [{ pageX, pageY }] = e.touches;
  } else {
    ({ pageX, pageY } = e);
  }

  return {
    x: pageX,
    y: pageY
  };
}

function clamp(num: number, min: number, max: number): number {
  return Math.min(Math.max(num, min), max);
}

function isCropValid(crop: CropShape) {
  return crop && !isNaN(crop.width) && !isNaN(crop.height);
}

function inverseOrd(ord: OrdShape) {
  if ('n' === ord) return 's';

  if ('ne' === ord) return 'sw';

  if ('e' === ord) return 'w';

  if ('se' === ord) return 'nw';

  if ('s' === ord) return 'n';

  if ('sw' === ord) return 'ne';

  if ('w' === ord) return 'e';

  if ('nw' === ord) return 'se';

  return ord;
}

function makeAspectCrop(
  crop: CropShape,
  imageWidth: number,
  imageHeight: number
): CropShape {
  if (isNaN(crop.aspect)) {
    // console.warn('`crop.aspect` should be a number in order to make an aspect crop', crop);

    return crop;
  }

  const completeCrop = {
    unit: 'px',
    x: 0,
    y: 0,
    ...crop
  };

  if (crop.width) {
    completeCrop.height = completeCrop.width / crop.aspect;
  }

  if (crop.height) {
    completeCrop.width = completeCrop.height * crop.aspect;
  }

  if (completeCrop.y + completeCrop.height > imageHeight) {
    completeCrop.height = imageHeight - completeCrop.y;
    completeCrop.width = completeCrop.height * crop.aspect;
  }

  if (completeCrop.x + completeCrop.width > imageWidth) {
    completeCrop.width = imageWidth - completeCrop.x;
    completeCrop.height = completeCrop.width / crop.aspect;
  }

  return completeCrop;
}

function convertToPercentCrop(
  crop: CropShape,
  imageWidth: number,
  imageHeight: number
): CropShape {
  if ('%' === crop.unit) {
    return crop;
  }

  return {
    unit: '%',
    aspect: crop.aspect,
    x: (crop.x / imageWidth) * 100,
    y: (crop.y / imageHeight) * 100,
    width: crop.width ? (crop.width / imageWidth) * 100 : 100,
    height: crop.height ? (crop.height / imageHeight) * 100 : 100
  };
}

function convertToPixelCrop(
  crop: CropShape,
  imageWidth: number,
  imageHeight: number
): CropShape {
  if ('px' === crop.unit) {
    return crop;
  }

  return {
    unit: 'px',
    aspect: crop.aspect,
    x: (crop.x * imageWidth) / 100,
    y: (crop.y * imageHeight) / 100,
    width: crop.width ? (crop.width * imageWidth) / 100 : imageWidth,
    height: crop.height ? (crop.height * imageHeight) / 100 : imageHeight
  };
}

function resolveCrop(
  pixelCrop: CropShape,
  imageWidth: number,
  imageHeight: number
) {
  if (pixelCrop.aspect && (!pixelCrop.width || !pixelCrop.height)) {
    return makeAspectCrop(pixelCrop, imageWidth, imageHeight);
  }

  return pixelCrop;
}

function containCrop(
  prevCrop: CropShape,
  crop: CropShape,
  imageWidth: number,
  imageHeight: number
): CropShape {
  const pixelCrop = convertToPixelCrop(crop, imageWidth, imageHeight);
  const prevPixelCrop = convertToPixelCrop(prevCrop, imageWidth, imageHeight);
  const contained = { ...pixelCrop };

  // Non-aspects are simple
  if (!pixelCrop.aspect) {
    if (0 > pixelCrop.x) {
      contained.x = 0;
      contained.width += pixelCrop.x;
    } else if (pixelCrop.x + pixelCrop.width > imageWidth) {
      contained.width = imageWidth - pixelCrop.x;
    }

    if (pixelCrop.y + pixelCrop.height > imageHeight) {
      contained.height = imageHeight - pixelCrop.y;
    }

    return contained;
  }

  let adjustedForX = false;

  if (0 > pixelCrop.x) {
    contained.x = 0;
    contained.width += pixelCrop.x;
    contained.height = contained.width / pixelCrop.aspect;
    adjustedForX = true;
  } else if (pixelCrop.x + pixelCrop.width > imageWidth) {
    contained.width = imageWidth - pixelCrop.x;
    contained.height = contained.width / pixelCrop.aspect;
    adjustedForX = true;
  }

  // If sizing in up direction we need to pin Y at the point it
  // would be at the boundary.
  if (adjustedForX && prevPixelCrop.y > contained.y) {
    contained.y = pixelCrop.y + (pixelCrop.height - contained.height);
  }

  let adjustedForY = false;

  if (contained.y + contained.height > imageHeight) {
    contained.height = imageHeight - pixelCrop.y;
    contained.width = contained.height * pixelCrop.aspect;
    adjustedForY = true;
  }

  // If sizing in left direction we need to pin X at the point it
  // would be at the boundary.
  if (adjustedForY && prevPixelCrop.x > contained.x) {
    contained.x = pixelCrop.x + (pixelCrop.width - contained.width);
  }

  return contained;
}

const defaultCrops: CropShape = {
  x: 0,
  y: 0,
  unit: 'px',
  aspect: 0
};

const ReactCrop = {
  xOrds: ['e', 'w'],
  yOrds: ['n', 's'],
  xyOrds: ['nw', 'ne', 'se', 'sw'],
  nudgeStep: 1,
  nudgeStepMedium: 10,
  nudgeStepLarge: 100
};

export default function useCropImage({
  imageContainerRef,
  imageWrapperRef,
  cropSelectionRef,
  imageRef,
  crop: cropOptions,
  minWidth = 0,
  maxWidth = 0,
  minHeight = 0,
  maxHeight = 0,
  disabled,
  locked,
  keepSelection,
  onComplete,
  onChange,
  onDragStart,
  onDragEnd
  
}: CropSelectionConfig) {
  const imageContainer = imageContainerRef.current;
  const evData = React.useRef<EvData>();
  const [cropState, setCropState] = React.useState<any>({});
  const mouseDownOnCrop = React.useRef<boolean>(false);
  const dragStarted = React.useRef<boolean>(false);
  const cropRef = React.useRef<CropShape>(
    Object.assign({}, defaultCrops, cropOptions)
  );

  const getMediaDimensions = React.useCallback(() => {
    if (!imageWrapperRef.current) {
      return { width: 0, height: 0 };
    }

    const { clientWidth, clientHeight } = imageWrapperRef.current;

    return { width: clientWidth, height: clientHeight };
  }, [imageWrapperRef]);

  const makeNewCrop = React.useCallback(
    (unit: CropUnit) => {
      const crop = cropRef.current;
      const { width, height } = getMediaDimensions();

      return 'px' === unit
        ? convertToPixelCrop(crop, width, height)
        : convertToPercentCrop(crop, width, height);
    },
    [getMediaDimensions]
  );

  const getDocumentOffset = React.useCallback(() => {
    const { clientTop = 0, clientLeft = 0 } =
      window.document.documentElement || {};

    return { clientTop, clientLeft };
  }, []);

  const getWindowOffset = React.useCallback(() => {
    const { pageYOffset = 0, pageXOffset = 0 } = window;

    return { pageYOffset, pageXOffset };
  }, []);

  const getElementOffset = React.useCallback(
    (el: HTMLElement) => {
      const rect = el.getBoundingClientRect();
      const doc = getDocumentOffset();
      const win = getWindowOffset();
      const top = rect.top + win.pageYOffset - doc.clientTop;
      const left = rect.left + win.pageXOffset - doc.clientLeft;

      return { top, left };
    },
    [getDocumentOffset, getWindowOffset]
  );

  const getCropStyle = React.useCallback(() => {
    const crop = makeNewCrop('%');

    return {
      top: `${crop.y}${crop.unit}`,
      left: `${crop.x}${crop.unit}`,
      width: `${crop.width}${crop.unit}`,
      height: `${crop.height}${crop.unit}`
    };
  }, [makeNewCrop]);

  const getNewSize = React.useCallback(() => {
    const { width, height } = getMediaDimensions();
    const crop = cropRef.current;

    // New width.
    let newWidth: number = evData.current.cropStartWidth + evData.current.xDiff;

    if (evData.current.xCrossOver) {
      newWidth = Math.abs(newWidth);
    }

    newWidth = clamp(newWidth, minWidth, maxWidth || width);

    // New height.
    let newHeight: number;

    if (crop.aspect) {
      newHeight = newWidth / crop.aspect;
    } else {
      newHeight = evData.current.cropStartHeight + evData.current.yDiff;
    }

    if (evData.current.yCrossOver) {
      // Cap if polarity is inversed and the height fills the y space.
      newHeight = Math.min(Math.abs(newHeight), evData.current.cropStartY);
    }

    newHeight = clamp(newHeight, minHeight, maxHeight || height);

    if (crop.aspect) {
      newWidth = clamp(newHeight * crop.aspect, 0, width);
    }

    return {
      width: newWidth,
      height: newHeight
    };
  }, [getMediaDimensions, maxHeight, maxWidth, minHeight, minWidth]);

  const dragCrop = React.useCallback(() => {
    const nextCrop = makeNewCrop('px');
    const { width, height } = getMediaDimensions();

    nextCrop.x = clamp(
      evData.current.cropStartX + evData.current.xDiff,
      0,
      width - nextCrop.width
    );
    nextCrop.y = clamp(
      evData.current.cropStartY + evData.current.yDiff,
      0,
      height - nextCrop.height
    );

    return nextCrop;
  }, [getMediaDimensions, makeNewCrop]);

  const onFulfillSelection = React.useCallback(() => {
    const nextCrop = makeNewCrop('px');
    const { width, height } = getMediaDimensions();

    nextCrop.width = width;
    nextCrop.height = height;
    nextCrop.x = 0;
    nextCrop.y = 0;

    if (onChange)
      onChange(
        convertToPixelCrop(nextCrop, width, height),
        convertToPercentCrop(nextCrop, width, height)
      );

    if (nextCrop.width && nextCrop.height) {
      const { left, top, width, height } = getCropStyle();
      cropSelectionRef.current.style.left = left;
      cropSelectionRef.current.style.top = top;
      cropSelectionRef.current.style.width = width;
      cropSelectionRef.current.style.height = height;
    }
  }, [
    cropSelectionRef,
    getCropStyle,
    getMediaDimensions,
    makeNewCrop,
    onChange
  ]);
  const crossOverCheck = React.useCallback(() => {
    if (
      !minWidth &&
      ((!evData.current.xCrossOver &&
        0 <= -Math.abs(evData.current.cropStartWidth) - evData.current.xDiff) ||
        (evData.current.xCrossOver &&
          0 >= -Math.abs(evData.current.cropStartWidth) - evData.current.xDiff))
    ) {
      evData.current.xCrossOver = !evData.current.xCrossOver;
    }

    if (
      !minHeight &&
      ((!evData.current.yCrossOver &&
        0 <=
        -Math.abs(evData.current.cropStartHeight) - evData.current.yDiff) ||
        (evData.current.yCrossOver &&
          0 >=
          -Math.abs(evData.current.cropStartHeight) - evData.current.yDiff))
    ) {
      evData.current.yCrossOver = !evData.current.yCrossOver;
    }

    const swapXOrd =
      evData.current.xCrossOver !== evData.current.startXCrossOver;
    const swapYOrd =
      evData.current.yCrossOver !== evData.current.startYCrossOver;

    evData.current.inversedXOrd = swapXOrd
      ? inverseOrd(evData.current.ord)
      : false;
    evData.current.inversedYOrd = swapYOrd
      ? inverseOrd(evData.current.ord)
      : false;
  }, [minHeight, minWidth]);

  const resizeCrop = React.useCallback(() => {
    const nextCrop = makeNewCrop('px');
    const { ord } = evData.current;

    // On the inverse change the diff so it's the same and
    // the same algo applies.
    if (evData.current.xInversed) {
      evData.current.xDiff -= evData.current.cropStartWidth * 2;
      evData.current.xDiffPc -= evData.current.cropStartWidth * 2;
    }

    if (evData.current.yInversed) {
      evData.current.yDiff -= evData.current.cropStartHeight * 2;
      evData.current.yDiffPc -= evData.current.cropStartHeight * 2;
    }

    // New size.
    const newSize = getNewSize();

    // Adjust x/y to give illusion of 'staticness' as width/height is increased
    // when polarity is inversed.
    let newX = evData.current.cropStartX;
    let newY = evData.current.cropStartY;

    if (evData.current.xCrossOver) {
      newX = nextCrop.x + (nextCrop.width - newSize.width);
    }

    if (evData.current.yCrossOver) {
      // This not only removes the little "shake" when inverting at a diagonal, but for some
      // reason y was way off at fast speeds moving sw->ne with fixed aspect only, I couldn't
      // figure out why.
      if (false === evData.current.lastYCrossover) {
        newY = nextCrop.y - newSize.height;
      } else {
        newY = nextCrop.y + (nextCrop.height - newSize.height);
      }
    }

    const crop = cropRef.current;
    const { width, height } = getMediaDimensions();
    const containedCrop = containCrop(
      crop,
      {
        unit: nextCrop.unit,
        x: newX,
        y: newY,
        width: newSize.width,
        height: newSize.height,
        aspect: nextCrop.aspect
      },
      width,
      height
    );

    // Apply x/y/width/height changes depending on ordinate (fixed aspect always applies both).
    if (nextCrop.aspect || -1 < ReactCrop.xyOrds.indexOf(ord)) {
      nextCrop.x = containedCrop.x;
      nextCrop.y = containedCrop.y;
      nextCrop.width = containedCrop.width;
      nextCrop.height = containedCrop.height;
    } else if (-1 < ReactCrop.xOrds.indexOf(ord)) {
      nextCrop.x = containedCrop.x;
      nextCrop.width = containedCrop.width;
    } else if (-1 < ReactCrop.yOrds.indexOf(ord)) {
      nextCrop.y = containedCrop.y;
      nextCrop.height = containedCrop.height;
    }

    evData.current.lastYCrossover = evData.current.yCrossOver;
    crossOverCheck();

    return nextCrop;
  }, [crossOverCheck, getMediaDimensions, getNewSize, makeNewCrop]);

  const onDocMouseTouchMove = useCallback(e => {
    const crop = cropRef.current;

    if (disabled) {
      return;
    }

    if (!mouseDownOnCrop.current) {
      return;
    }

    e.preventDefault(); // Stop drag selection.

    if (!dragStarted.current) {
      dragStarted.current = true;

      if (onDragStart) onDragStart(e);
    }

    const clientPos = getClientPos(e);

    if (evData.current.isResize && crop.aspect && evData.current.cropOffset) {
      clientPos.y = straightenYPath(clientPos.x);
    }

    evData.current.xDiff = clientPos.x - evData.current.clientStartX;
    evData.current.yDiff = clientPos.y - evData.current.clientStartY;

    let nextCrop: CropShape;

    if (evData.current.isResize) {
      nextCrop = resizeCrop();
    } else {
      nextCrop = dragCrop();
    }

    if (nextCrop !== crop) {
      const { width, height } = getMediaDimensions();

      if (onChange)
        onChange(
          convertToPixelCrop(nextCrop, width, height),
          convertToPercentCrop(nextCrop, width, height)
        );
    }

    if (nextCrop.width && nextCrop.height) {
      if (!cropSelectionRef.current) return;

      const { left, top, width, height } = getCropStyle();
      cropSelectionRef.current.style.left = left;
      cropSelectionRef.current.style.top = top;
      cropSelectionRef.current.style.width = width;
      cropSelectionRef.current.style.height = height;
    }
  }, []);

  const onDocMouseTouchEnd = React.useCallback(
    e => {
      unbindDocMove();

      if (disabled) {
        return;
      }

      const crop = cropRef.current;

      if (mouseDownOnCrop.current) {
        mouseDownOnCrop.current = false;
        dragStarted.current = false;

        const { width, height } = getMediaDimensions();

        if (onDragEnd) onDragEnd(e);

        if (onComplete)
          onComplete(
            convertToPixelCrop(crop, width, height),
            convertToPercentCrop(crop, width, height)
          );

        setCropState(prev => ({
          ...prev,
          cropIsActive: false,
          newCropIsBeingDrawn: false
        }));
      }
    },
    [disabled, getMediaDimensions, onComplete, onDragEnd, unbindDocMove]
  );

  var unbindDocMove = React.useCallback(() => {
    if (!imageContainer) return;
    imageContainer.removeEventListener('mousemove', onDocMouseTouchMove);
    imageContainer.removeEventListener('touchmove', onDocMouseTouchMove);

    imageContainer.removeEventListener('mouseup', onDocMouseTouchEnd);
    imageContainer.removeEventListener('touchend', onDocMouseTouchEnd);
    imageContainer.removeEventListener('touchcancel', onDocMouseTouchEnd);
  }, [imageContainer, onDocMouseTouchEnd, onDocMouseTouchMove]);

  const straightenYPath = React.useCallback((clientX: number) => {
    const { ord, cropOffset, cropStartWidth, cropStartHeight } = evData.current;
    let kValue: number;
    let dValue: number;

    if ('nw' === ord || 'se' === ord) {
      kValue = cropStartHeight / cropStartWidth;
      dValue = cropOffset.top - cropOffset.left * kValue;
    } else {
      kValue = -cropStartHeight / cropStartWidth;
      dValue = cropOffset.top + (cropStartHeight - cropOffset.left * kValue);
    }

    return kValue * clientX + dValue;
  }, []);

  const bindDocMove = React.useCallback(() => {

    imageContainer.addEventListener('mousemove', onDocMouseTouchMove);
    imageContainer.addEventListener('touchmove', onDocMouseTouchMove, {
      passing: false
    });

    imageContainer.addEventListener('mouseup', onDocMouseTouchEnd);
    imageContainer.addEventListener('touchend', onDocMouseTouchEnd, {
      passing: false
    });
    imageContainer.addEventListener('touchcancel', onDocMouseTouchEnd, {
      passing: false
    });
  }, [imageContainer, onDocMouseTouchEnd, onDocMouseTouchMove]);

  const onCropMouseTouchDown = e => {
    const crop = cropRef.current;
    const { width, height } = getMediaDimensions();
    const pixelCrop = convertToPixelCrop(crop, width, height);

    if (disabled) {
      return;
    }

    e.preventDefault(); // Stop drag selection.

    // Bind to doc to follow movements outside of element.
    bindDocMove();

    const clientPos = getClientPos(e);

    // Focus for detecting keypress.
    if (imageContainerRef.current.setActive) {
      imageContainerRef.current.setActive({ preventScroll: true }); // IE/Edge #289
    } else {
      imageContainerRef.current.focus({ preventScroll: true }); // All other browsers
    }

    const { ord } = e.target.dataset;
    const xInversed = 'nw' === ord || 'w' === ord || 'sw' === ord;
    const yInversed = 'nw' === ord || 'n' === ord || 'ne' === ord;

    let cropOffset: any;

    if (pixelCrop.aspect) {
      cropOffset = getElementOffset(cropSelectionRef.current);
    }

    evData.current = {
      clientStartX: clientPos.x,
      clientStartY: clientPos.y,
      cropStartWidth: pixelCrop.width,
      cropStartHeight: pixelCrop.height,
      cropStartX: xInversed ? pixelCrop.x + pixelCrop.width : pixelCrop.x,
      cropStartY: yInversed ? pixelCrop.y + pixelCrop.height : pixelCrop.y,
      xInversed,
      yInversed,
      xCrossOver: xInversed,
      yCrossOver: yInversed,
      startXCrossOver: xInversed,
      startYCrossOver: yInversed,
      isResize: e.target.dataset.ord,
      ord,
      cropOffset,
      xDiff: 0,
      yDiff: 0,
      xDiffPc: 0,
      yDiffPc: 0
    };

    mouseDownOnCrop.current = true;

    setCropState(prev => ({
      ...prev,
      cropIsActive: true
    }));
  };

  const onComponentMouseTouchDown = e => {
    const crop = cropRef.current;

    if (!imageWrapperRef.current) return;

    const componentEl = imageWrapperRef.current.firstChild;

    if (e.target !== componentEl || !componentEl.contains(e.target)) {
      return;
    }

    if (disabled || locked || (keepSelection && isCropValid(crop))) {
      return;
    }

    e.preventDefault(); // Stop drag selection.

    // Bind to doc to follow movements outside of element.
    bindDocMove();

    const clientPos = getClientPos(e);

    // Focus for detecting keypress.
    if (imageContainerRef.current.setActive) {
      imageContainerRef.current.setActive({ preventScroll: true }); // IE/Edge #289
    } else {
      imageContainerRef.current.focus({ preventScroll: true }); // All other browsers
    }

    const mediaOffset = getElementOffset(imageWrapperRef.current);
    const x = clientPos.x - mediaOffset.left;
    const y = clientPos.y - mediaOffset.top;

    const nextCrop: CropShape = {
      unit: 'px',
      aspect: crop ? crop.aspect : undefined,
      x,
      y,
      width: 0,
      height: 0
    };

    evData.current = {
      clientStartX: clientPos.x,
      clientStartY: clientPos.y,
      cropStartWidth: nextCrop.width,
      cropStartHeight: nextCrop.height,
      cropStartX: nextCrop.x,
      cropStartY: nextCrop.y,
      xInversed: false,
      yInversed: false,
      xCrossOver: false,
      yCrossOver: false,
      startXCrossOver: false,
      startYCrossOver: false,
      isResize: true,
      ord: 'nw',
      xDiff: 0,
      yDiff: 0,
      xDiffPc: 0,
      yDiffPc: 0
    };

    mouseDownOnCrop.current = true;

    const { width, height } = getMediaDimensions();

    if (onChange)
      onChange(
        convertToPixelCrop(nextCrop, width, height),
        convertToPercentCrop(nextCrop, width, height)
      );

    setCropState(prev => ({
      ...prev,
      cropIsActive: true,
      newCropIsBeingDrawn: true
    }));
  };

  const getCropValue = React.useCallback(() => {
    const cropped = makeNewCrop('%');
    const modified =
      0 !== cropped.x ||
      0 !== cropped.y ||
      100 !== cropped.height ||
      100 !== cropped.width;

    return [modified, cropped];
  }, [makeNewCrop]);

  return {
    getCropStyle,
    cropSelectionStyle: getCropStyle(),
    onFulfillSelection,
    getCropValue,
    onCropMouseTouchDown,
    onComponentMouseTouchDown
  };
}
