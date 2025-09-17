import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const errorMsg = 'Required';

const schema: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  required: true,
  errors: {
    required: errorMsg
  }
};

const yupSchema = toYup(schema) as NumberSchema;

test('boolean expect fail', async () => {
  expect(yupSchema.isValidSync(-5.1)).toBe(false);
  expect(yupSchema.isValidSync('hello')).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync([true])).toBe(false);
  expect(yupSchema.isValidSync([false])).toBe(false);
});

test('boolean expect pass', async () => {
  expect(yupSchema.isValidSync(true)).toBe(true);
  expect(yupSchema.isValidSync(false)).toBe(true);
});

test('boolean expect fail message', async () => {
  const [error] = await to(yupSchema.validate(undefined));
  expect((error as ValidationError).message).toBe(errorMsg);
});
