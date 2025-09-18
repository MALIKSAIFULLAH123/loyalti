import { isEmpty, isNil } from 'lodash';

export const calculatorPosition2Persent = (
  _position: { x: number; y: number },
  containerRect: { width: number; height: number },
  contentRect: { width: number; height: number },
  rotation = 0,
  offset = 0
) => {
  if (isEmpty(_position)) return { top: '60%', right: '0%' };

  const { width: containerWidth, height: containerHeight } = containerRect;
  const contentWidth = contentRect?.width;
  const contentHeight = contentRect?.height;
  const position = { ..._position };

  const midPointX = containerWidth / 2;
  const midPointY = containerHeight / 2;

  if (rotation % 180 !== 0) {
    position.x = position.x - offset;
    position.y = position.y + offset;
  }

  const isBottom = midPointY < position.y + contentHeight / 2;
  const isRight = midPointX < position.x + contentWidth / 2;

  const result: any = {};

  if (isBottom) {
    const distanceFromBottom = containerHeight - position.y - contentHeight;
    result.bottom = `${(distanceFromBottom / containerHeight) * 100}%`;
  } else {
    result.top = `${(position.y / containerHeight) * 100}%`;
  }

  if (isRight) {
    const distanceFromRight = containerWidth - position.x - contentWidth;
    result.right = `${(distanceFromRight / containerWidth) * 100}%`;
  } else {
    result.left = `${(position.x / containerWidth) * 100}%`;
  }

  return isEmpty(result) ? { top: '60%', right: '0%' } : result;
};

export const parsePersent2Number = number => {
  const result = `${number}`.includes('%')
    ? Number(number.split('%')?.[0])
    : number ?? 0;

  if (isNil(result)) return false;

  return result;
};

export const calculatorPersent2Position = (
  position: { top?: string; bottom?: string; left?: string; right?: string },
  containerRect: { width: number; height: number },
  {
    widthContent,
    heightContent
  }: { widthContent: number; heightContent: number },
  rotation = 0,
  offset = 0
) => {
  if (isEmpty(position)) return { x: 0, y: 0 };

  const result: any = {};

  const top = parsePersent2Number(position?.top);
  const bottom = parsePersent2Number(position?.bottom);
  const left = parsePersent2Number(position?.left);
  const right = parsePersent2Number(position?.right);

  const width = containerRect?.width;
  const height = containerRect?.height;

  let offsetX = offset;
  let offsetY = offset;

  if (!isNil(position?.left)) {
    result.x = (left / 100) * width;

    if (rotation % 180 !== 0) {
      offsetX = -offset;
      offsetY = -offset;

      if (left === 0) {
        offsetY = offset;
      }
    }
  }

  if (!isNil(position?.right)) {
    const rightRect = (right / 100) * width;
    result.x = width - rightRect - widthContent;

    if (rotation % 180 !== 0) {
      offsetY = -offset;
    }
  }

  if (!isNil(position?.top)) {
    result.y = (top / 100) * height;

    if (rotation % 180 !== 0) {
      offsetY = -offset;

      if (top === 0) {
        offsetY = -offset;
      }
    }
  }

  if (!isNil(position?.bottom)) {
    const bottomRect = (bottom / 100) * height;
    result.y = height - bottomRect - heightContent;

    if (rotation % 180 !== 0) {
      offsetY = offset;
    }
  }

  if (rotation % 180 !== 0) {
    result.x = result.x - offsetX;
    result.y = result.y + offsetY;
  }

  if (isEmpty(result)) return { x: 0, y: 0 };

  return result;
};

export const mappingRotate = {
  0: 'rotate(0deg) translate(0, 0)',
  90: 'rotate(90deg) translate(0%, -100%)',
  180: 'rotate(180deg) translate(-100%, -100%)',
  270: 'rotate(270deg) translate(-100%, 0%)'
};

export const calculatePositionWithBoundsRotate = (
  currentPosition: { x: number; y: number },
  containerRect: { width: number; height: number },
  rotate: number,
  offset: number,
  {
    widthContent,
    heightContent
  }: { widthContent: number; heightContent: number }
) => {
  const newPosition = { ...currentPosition };
  const isRotated = rotate % 180 !== 0;

  if (isRotated) {
    newPosition.x = Math.max(
      offset,
      Math.min(newPosition.x, containerRect.width - heightContent + offset)
    );
    newPosition.y = Math.max(
      -offset,
      Math.min(newPosition.y, containerRect.height - widthContent - offset)
    );
  } else {
    newPosition.x = Math.max(
      0,
      Math.min(newPosition.x, containerRect.width - widthContent)
    );
    newPosition.y = Math.max(
      0,
      Math.min(newPosition.y, containerRect.height - heightContent)
    );
  }

  return newPosition;
};

export const transformPositionStyle = (
  position: { top?: string; bottom?: string; left?: string; right?: string },
  contentRect: { width: number; height: number },
  rotation = 0
) => {
  let result: any = 'translate(0, 0)';

  if (isEmpty(position) || rotation % 180 === 0) return result;

  const top = parsePersent2Number(position?.top);
  const bottom = parsePersent2Number(position?.bottom);

  const width = contentRect?.width;
  const height = contentRect?.height;

  let widthX = contentRect?.width / 2;
  let heightX = contentRect?.height / 2;
  let widthY = contentRect?.width / 2;
  let heightY = contentRect?.height / 2;

  if (!isNil(position?.left)) {
    widthX = 0;
    heightX = width / 2 - height / 2;

    if (!isNil(position?.top)) {
      if (top === 0) {
        widthY = 0;
        heightY = width / 2 - height / 2;
        result = `translate(calc(${widthX}px - ${heightX}px), calc(${widthY}px + ${heightY}px)) !important`;
      } else {
        result = `translate(calc(${widthX}px - ${heightX}px), calc(${widthY}px - ${heightY}px)) !important`;
      }
    }

    if (!isNil(position?.bottom)) {
      result = `translate(calc(${widthX}px - ${heightX}px), calc(-${widthY}px + ${heightY}px)) !important`;
    }
  }

  if (!isNil(position?.right)) {
    if (!isNil(position?.top)) {
      result = `translate(calc(${widthX}px - ${heightX}px), calc(${widthY}px - ${heightY}px)) !important`;
    }

    if (!isNil(position?.bottom)) {
      if (bottom === 0) {
        widthX = 0;
        heightX = width / 2 - height / 2;
        result = `translate(calc(${widthX}px + ${heightX}px), calc(-${widthY}px + ${heightY}px)) !important`;
      } else {
        result = `translate(calc(${widthX}px - ${heightX}px), calc(-${widthY}px + ${heightY}px)) !important`;
      }
    }
  }

  return result;
};
