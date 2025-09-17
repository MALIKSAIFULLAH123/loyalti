import { createContext } from 'react';

type State = Record<string, string>;

const ToggleGroupContext = createContext<
  [State, React.Dispatch<React.SetStateAction<State>>]
>([{}, undefined]);

export default ToggleGroupContext;
