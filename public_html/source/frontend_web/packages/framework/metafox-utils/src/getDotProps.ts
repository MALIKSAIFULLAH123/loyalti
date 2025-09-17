import inspectDotProps from './inspectDotProps';

export default function getDotProps(values: Record<string, any>): string[] {
  const props = {};
  inspectDotProps(values, true, props);

  return Object.keys(props).filter(Boolean);
}
