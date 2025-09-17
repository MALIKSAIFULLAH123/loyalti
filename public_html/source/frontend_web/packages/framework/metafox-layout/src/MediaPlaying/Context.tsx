import { createContext } from 'react';

type State = Record<string, any>;

const MediaPlayingContext = createContext<
  [State, React.Dispatch<React.SetStateAction<State>>]
>([{}, undefined]);

export default MediaPlayingContext;
