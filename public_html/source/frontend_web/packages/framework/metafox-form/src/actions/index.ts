export function openMultiStepFormAction(payload: Record<string, any>) {
  return { type: 'multiStepForm/init', payload };
}
