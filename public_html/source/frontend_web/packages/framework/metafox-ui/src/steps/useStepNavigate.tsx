import { useContext } from 'react';
import StepContext from './Context';

export default function useStepNavigate() {
  const context = useContext(StepContext);

  // no context.
  if (!context) {
    return [() => {}, () => {}];
  }

  const [, dispatch] = context;

  return [() => dispatch({ type: 'next' }), () => dispatch({ type: 'back' })];
}
