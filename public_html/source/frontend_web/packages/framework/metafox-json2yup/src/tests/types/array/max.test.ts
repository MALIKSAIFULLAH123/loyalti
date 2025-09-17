import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ArrayTypeSchema } from '../../../types';

const errorMsg = 'Max 4 items';

const schema: ArrayTypeSchema = {
  type: 'array',
  strict: true,
  required: true,
  max: 4,
  errors: {
    max: errorMsg
  }
};

const yupSchema = toYup(schema);

test('max expect fail', async () => {
  expect(yupSchema.isValidSync([1, 2, 3, 4, 5, 6])).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('max expect pass', async () => {
  expect(yupSchema.isValidSync([1])).toBe(true);
  expect(yupSchema.isValidSync([1, 2])).toBe(true);
  expect(yupSchema.isValidSync([1, 2, 3, 4])).toBe(true);
  expect(yupSchema.isValidSync([1, 2, 3, 4])).toBe(true);
});

test('max expect fail message', async () => {
  const [error] = await to(yupSchema.validate([1, 2, 3, 4, 5]));
  expect((error as ValidationError).message).toBe(errorMsg);
});
