import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Must be negative';

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  sign: 'negative',
  errors: {
    negative: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('negative expect fail', async () => {
  expect(yupSchema.isValidSync(4)).toBe(false);
  expect(yupSchema.isValidSync(1)).toBe(false);
  expect(yupSchema.isValidSync(0.0000001)).toBe(false);
  expect(yupSchema.isValidSync(Number.MAX_SAFE_INTEGER)).toBe(false);
});

test('negative expect pass', async () => {
  expect(yupSchema.isValidSync(-5)).toBe(true);
  expect(yupSchema.isValidSync(-0.0000001)).toBe(true);
  expect(yupSchema.isValidSync(Number.MIN_SAFE_INTEGER)).toBe(true);
});

test('negative expect fail message', async () => {
  const [error] = await to(yupSchema.validate(1));
  expect((error as ValidationError).message).toBe(errorMsg);
});
