import produce, { Draft } from 'immer';

export interface State {
  readonly defaultImage: string;
  readonly left: number;
  readonly top: number;
  imgHeight: number;
  wrapHeight: number;
  image: string;
  menuOpen: boolean;
  dragging: boolean;
  enable: boolean;
  position: { x: number; y: number };
  imageDetail?: Record<string, any>;
  file?: File;
  tempFile?: Record<string, any>;
  loadingParseFile?: boolean;
  bounds: {
    top: number;
    left: number;
    right: number;
    bottom: number;
  };
}

type Action =
  | { type: 'reposition' }
  | { type: 'success'; payload: { image: string; top: string } }
  | {
      type: 'defaultImage';
      payload: { defaultImage: string; position?: { x: number; y: number } };
    }
  | { type: 'failed' }
  | { type: 'saving' }
  | { type: 'closeMenu' }
  | { type: 'toggleMenu' }
  | { type: 'openMenu' }
  | { type: 'setFile'; payload: File }
  | { type: 'loadingParseFile'; payload: boolean }
  | { type: 'dragging'; payload: { x: number; y: number } }
  | { type: 'cancel' }
  | {
      type: 'setImgHeight';
      payload: { imgHeight: number; wrapHeight: number; top: number };
    }
  | { type: 'setTempFile'; payload: Record<string, any> }
  | { type: 'resetPosition' }
  | { type: 'setFromPhoto'; payload: Record<string, any> };

export const reducer = produce((draft: Draft<State>, action: Action) => {
  switch (action.type) {
    case 'success':
      draft.defaultImage = action.payload.image;
      draft.top = +action.payload.top;
      draft.file = undefined;
      draft.imageDetail = undefined;
      draft.dragging = false;
      break;
    case 'defaultImage':
      draft.defaultImage = action.payload.defaultImage;
      draft.image = action.payload.defaultImage;
      draft.position = action.payload.position;
      break;
    case 'openMenu':
      draft.menuOpen = true;
      break;
    case 'closeMenu':
      draft.menuOpen = false;
      break;
    case 'toggleMenu':
      draft.menuOpen = !draft.menuOpen;
      break;
    case 'reposition':
      draft.dragging = true;
      draft.menuOpen = false;
      break;
    case 'cancel':
      draft.dragging = false;
      draft.menuOpen = false;
      draft.position.x = draft.left;
      draft.position.y = draft.top;
      draft.image = draft.defaultImage;
      draft.file = undefined;
      draft.imageDetail = undefined;
      draft.tempFile = undefined;
      break;
    case 'dragging':
      draft.position = action.payload;
      break;
    case 'resetPosition':
      draft.position = { x: 0, y: 0 };
      draft.top = 0;
      break;
    case 'setImgHeight':
      draft.imgHeight = action.payload?.imgHeight;
      draft.wrapHeight = action.payload?.wrapHeight;
      draft.bounds.top = draft.wrapHeight - draft.imgHeight;
      draft.top = action.payload?.top;
      draft.position.y = action.payload?.top;
      break;
    case 'setFile':
      draft.file = action.payload;
      draft.image = URL.createObjectURL(draft.file);
      draft.position = { x: 0, y: 0 };
      draft.top = 0;
      draft.menuOpen = false;
      draft.dragging = true;
      break;
    case 'loadingParseFile':
      draft.loadingParseFile = action.payload;
      break;
    case 'setTempFile':
      draft.image = action.payload?.source;
      draft.tempFile = action.payload?.tempFile;
      draft.loadingParseFile = false;
      break;
    case 'failed':
      draft.dragging = false;
      draft.menuOpen = false;
      draft.position.x = draft.left;
      draft.position.y = draft.top;
      draft.image = draft.defaultImage;
      draft.file = undefined;
      draft.imageDetail = undefined;
      break;
    case 'setFromPhoto':
      draft.imageDetail = action.payload;
      draft.image = action.payload.image.origin;
      draft.position = { x: 0, y: 0 };
      draft.top = 0;
      draft.menuOpen = false;
      draft.dragging = true;
      break;
    case 'saving':
      draft.menuOpen = false;
  }
});
