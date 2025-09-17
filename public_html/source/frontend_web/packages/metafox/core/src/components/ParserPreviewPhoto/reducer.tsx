import produce, { Draft } from 'immer';

export interface State {
  imageError: boolean;
  loading: boolean;
  progress: number;
}

type Action =
  | { type: 'setLoading'; payload: boolean }
  | { type: 'setProgress'; payload: number }
  | { type: 'setImageError'; payload: boolean }
  | { type: 'setLoaded' };

export const reducer = produce((draft: Draft<State>, action: Action) => {
  switch (action.type) {
    case 'setLoading':
      draft.loading = action.payload;
      break;
    case 'setProgress':
      draft.progress = action.payload;
      break;
    case 'setImageError':
      draft.imageError = action.payload;
      break;
    case 'setLoaded':
      draft.progress = 100;
      draft.loading = false;
      draft.imageError = false;
      break;
  }
});
