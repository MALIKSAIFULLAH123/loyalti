import { useContext } from 'react';
import StepContext from './Context';

export default function useStepContext() {
  return useContext(StepContext);
}
