import { createContext } from 'react';

type State = boolean;

const InAppBarContext = createContext<
  [State, React.Dispatch<React.SetStateAction<State>>]
>([false, undefined]);

export default InAppBarContext;
