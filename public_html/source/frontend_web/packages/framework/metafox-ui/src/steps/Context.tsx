import { createContext } from 'react';
import { State } from './types';

const StepContext =
  createContext<[state: State, dispatch?: Function]>(undefined);

export default StepContext;
