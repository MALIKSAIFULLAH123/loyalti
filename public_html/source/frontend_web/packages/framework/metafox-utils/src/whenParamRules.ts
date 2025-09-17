import when from './when';

const alwayAccepts = ['page', 'limit'];

export default function whenParamRules(
  input: Record<string, any>,
  rules?: Record<string, any>
): Record<string, any> {
  if (!rules) return input;

  const keys = Object.keys(rules);
  const alt: Record<string, string> = {};

  keys.forEach(name => {
    if (alwayAccepts.includes(name) || when(input, rules[name])) {
      if (input[name] !== undefined) {
        alt[name] = input[name];
      }
    }
  });

  return alt;
}
