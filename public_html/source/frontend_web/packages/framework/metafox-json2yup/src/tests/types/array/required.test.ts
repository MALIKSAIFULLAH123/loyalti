import to from 'await-to-js';
import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

const errorMsg = 'Array is required';

const schema: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  required: true,
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema);

test('required expect fail', async () => {
  expect(yupSchema.isValidSync([])).toBe(true);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
  expect(yupSchema.isValidSync('')).toBe(false);
  expect(yupSchema.isValidSync('hello')).toBe(false);
  expect(yupSchema.isValidSync(123)).toBe(false);
  expect(yupSchema.isValidSync(false)).toBe(false);
  expect(yupSchema.isValidSync(true)).toBe(false);
});

test('required expect pass', async () => {
  expect(yupSchema.isValidSync([1, 2])).toBe(true);
  expect(yupSchema.isValidSync(['1', '2'])).toBe(true);
});

test('required expect fail message', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect(error.message).toBe(errorMsg);
});
