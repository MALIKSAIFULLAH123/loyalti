import { Area } from './types';
import produce, { Draft } from 'immer';
import { isEqual } from 'lodash';
import { initState } from './CropAvatarDialog';

interface Position {
  x: number;
  y: number;
}

export interface State {
  crop: Position;
  zoom: number;
  flipHorizontal: boolean;
  flipVertical: boolean;
  imageSrc?: string;
  imageId?: number;
  croppedAreaPixels: Area;
  rotation: number;
  isLoading: boolean;
  isDirty: boolean;
  isSuccess: boolean;
  isLoadingParseFile?: boolean;
  progressParseFile?: number;
}

type Action =
  | {
      type: 'setCrop';
      payload: Position;
    }
  | {
      type: 'setCroppedAreaPixels';
      payload: Area;
    }
  | {
      type: 'setZoom';
      payload: { mode?: 'minus' | 'plus'; zoom?: number };
    }
  | {
      type: 'setFlipVertical';
    }
  | {
      type: 'setFlipHorizontal';
    }
  | {
      type: 'setImageSrc';
      payload: string;
    }
  | {
      type: 'successParseFile';
    }
  | {
      type: 'errorParseFile';
    }
  | {
      type: 'startParseFile';
    }
  | {
      type: 'setProgressParseFile';
      payload: number;
    }
  | {
      type: 'setFromPhoto';
      payload: Record<string, any>;
    }
  | {
      type: 'setRotation';
      payload: number;
    }
  | {
      type: 'setLoading';
      payload: boolean;
    }
  | { type: 'pendingSave' }
  | { type: 'fullfilSave' }
  | { type: 'rejectSave' }
  | { type: 'updateFile'; payload: string }
  | { type: 'setImgSource'; payload: string };

export const reducer = produce((draft: Draft<State>, action: Action) => {
  switch (action.type) {
    case 'setCrop':
      draft.crop = action.payload;
      break;
    case 'setFlipVertical':
      draft.flipVertical = !draft.flipVertical;
      break;
    case 'setFlipHorizontal':
      draft.flipHorizontal = !draft.flipHorizontal;
      break;
    case 'pendingSave':
      draft.isLoading = true;
      break;
    case 'fullfilSave':
      draft.isLoading = false;
      draft.isDirty = false;
      draft.isSuccess = true;

      return;
    case 'rejectSave':
      draft.isLoading = false;
      break;
    case 'startParseFile':
      draft.isLoadingParseFile = true;
      draft.progressParseFile = 0;
      break;
    case 'setProgressParseFile':
      draft.progressParseFile = action.payload;
      break;
    case 'successParseFile':
      draft.isLoadingParseFile = false;
      draft.progressParseFile = 100;
      break;
    case 'errorParseFile':
      draft.isLoadingParseFile = false;
      break;
    case 'updateFile':
      draft.imageSrc = action.payload;
      draft.imageId = null;
      draft.rotation = initState.rotation;
      draft.crop = initState.crop;
      draft.zoom = initState.zoom;
      break;
    case 'setFromPhoto':
      draft.imageSrc = action.payload.image.origin;
      draft.imageId = action.payload.id;
      draft.rotation = initState.rotation;
      draft.crop = initState.crop;
      draft.zoom = initState.zoom;
      break;
    case 'setImgSource':
      draft.imageSrc = action.payload;
      break;
    case 'setZoom': {
      const { mode, zoom } = action.payload;

      if (zoom) {
        draft.zoom = zoom;

        break;
      }

      let number = 0;

      if (mode === 'minus')
        number = 1 < draft.zoom - 0.2 ? draft.zoom - 0.2 : 1;

      if (mode === 'plus') number = 3 > draft.zoom + 0.2 ? draft.zoom + 0.2 : 3;

      draft.zoom = number;

      break;
    }
    case 'setCroppedAreaPixels':
      draft.croppedAreaPixels = action.payload;
      break;
    case 'setRotation':
      draft.rotation = action.payload;
      break;
    default: {
      break;
    }
  }

  const currentState = {
    crop: draft.crop,
    flipVertical: draft.flipVertical,
    flipHorizontal: draft.flipHorizontal,
    rotation: draft.rotation,
    zoom: draft.zoom,
    imageSrc: draft.imageSrc
  };

  const initialState = {
    crop: initState.crop,
    flipVertical: initState.flipVertical,
    flipHorizontal: initState.flipHorizontal,
    rotation: initState.rotation,
    zoom: initState.zoom,
    imageSrc: initState.imageSrc
  };

  draft.isDirty = !isEqual(initialState, currentState);
});
