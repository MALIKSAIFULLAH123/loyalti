import { MAXZOOM, MINZOOM } from '@metafox/story/constants';
import produce, { Draft } from 'immer';
import { isEqual, pick } from 'lodash';

interface Position {
  x: number;
  y: number;
}

export interface SizeImgProp {
  width: any;
  height: any;
}

interface Bound {
  top: number;
  bottom: number;
  left: number;
  right: number;
}

interface DragProp {
  position: Position;
  width?: number;
  height?: number;
}

interface InitProp {
  width: number;
  height: number;
  widthContainer: number;
  heightContainer: number;
  position?: Position;
}

export interface StateContentImage {
  width?: number;
  height?: number;
  widthContainer?: number;
  heightContainer?: number;
  bound: Bound;
  position: Position;
  zoom: number;
  imageSrc?: string;
  imageId?: number;
  rotation: number;
  isDirty: boolean;
}
const defaultBound = { top: -100, left: -100, right: 100, bottom: 100 };
export const initStateCrop: StateContentImage = {
  position: { x: 0, y: 0 },
  zoom: 1,
  rotation: 0,
  isDirty: false,
  bound: defaultBound
};

type Action =
  | {
      type: 'setInitImage';
      payload: InitProp;
    }
  | {
      type: 'setDrag';
      payload: DragProp;
    }
  | {
      type: 'setZoom';
      payload: { mode?: 'minus' | 'plus'; zoom?: number };
    }
  | {
      type: 'setRotation';
      payload: number;
    }
  | { type: 'setImgSource'; payload: string };

const calculatorBound = ({
  rotate,
  height,
  width,
  zoom,
  widthContainer,
  heightContainer
}) => {
  if (rotate % 4 === 0) {
    return {
      top: -(height * zoom) + 20,
      left: -(width * zoom) + 20,
      right: widthContainer - 20,
      bottom: heightContainer - 20
    };
  } else {
    return {
      top: -(width * zoom) + 20,
      left: -(height * zoom) + 20,
      right: widthContainer - 20,
      bottom: heightContainer - 20
    };
  }
};

const setPosition = draft => {
  if (draft.position.x < draft.bound.left) {
    draft.position = { ...draft.position, x: draft.bound.left };
  }

  if (draft.position.y < draft.bound.top) {
    draft.position = { ...draft.position, y: draft.bound.top };
  }
};

export const reducerCrop = produce(
  (draft: Draft<StateContentImage>, action: Action) => {
    switch (action.type) {
      case 'setInitImage': {
        const { width, height, widthContainer, heightContainer, position } =
          action.payload || {};

        draft.width = width;
        draft.height = height;
        draft.widthContainer = widthContainer;
        draft.heightContainer = heightContainer;

        draft.bound = {
          top: -height + 20,
          left: -width + 20,
          right: widthContainer - 20,
          bottom: heightContainer - 20
        };

        if (position) {
          draft.position = position;
        }

        break;
      }
      case 'setDrag': {
        const { position } = action.payload;

        draft.position = position;

        break;
      }
      case 'setImgSource':
        draft.imageSrc = action.payload;
        break;
      case 'setZoom': {
        const { mode, zoom } = action.payload || {};
        const width = draft?.width || 291;
        const height = draft?.height || 518;

        if (zoom) {
          draft.zoom = zoom;

          draft.bound = calculatorBound({
            rotate: draft.rotation,
            height,
            width,
            zoom,
            widthContainer: draft.widthContainer,
            heightContainer: draft.heightContainer
          });

          setPosition(draft);

          break;
        }

        let number = 0;

        if (mode === 'minus')
          number = MINZOOM < draft.zoom - 0.2 ? draft.zoom - 0.2 : MINZOOM;

        if (mode === 'plus')
          number = MAXZOOM > draft.zoom + 0.2 ? draft.zoom + 0.2 : MAXZOOM;

        draft.zoom = number;

        draft.bound = calculatorBound({
          rotate: draft.rotation,
          height,
          width,
          zoom: number,
          widthContainer: draft.widthContainer,
          heightContainer: draft.heightContainer
        });

        setPosition(draft);

        break;
      }
      case 'setRotation': {
        draft.rotation = action.payload;

        draft.bound = calculatorBound({
          rotate: action.payload,
          height: draft.height,
          width: draft.width,
          zoom: draft.zoom,
          widthContainer: draft.widthContainer,
          heightContainer: draft.heightContainer
        });

        setPosition(draft);

        break;
      }
      default: {
        break;
      }
    }

    const currentState = {
      position: draft.position,
      rotation: draft.rotation,
      zoom: draft.zoom,
      imageSrc: draft.imageSrc
    };

    const initialState = {
      position: initStateCrop.position,
      rotation: initStateCrop.rotation,
      zoom: initStateCrop.zoom,
      imageSrc: initStateCrop.imageSrc
    };

    draft.isDirty = !isEqual(
      pick(initialState, ['position', 'rotation', 'zoom']),
      pick(currentState, ['position', 'rotation', 'zoom'])
    );
  }
);
