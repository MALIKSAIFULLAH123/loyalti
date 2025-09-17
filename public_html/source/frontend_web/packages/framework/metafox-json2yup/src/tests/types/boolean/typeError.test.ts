import to from 'await-to-js';
import { BooleanSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const errorMsg = 'Has to be a boolean';
const schema: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  required: true,
  errors: {
    typeError: errorMsg
  }
};
const yupSchema = toYup(schema) as BooleanSchema;

test('typeError expect fail', async () => {
  expect(yupSchema.isValidSync(5)).toBe(false);
  expect(yupSchema.isValidSync('5')).toBe(false);
  expect(yupSchema.isValidSync([])).toBe(false);
  expect(yupSchema.isValidSync({})).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('typeError expect pass', async () => {
  expect(yupSchema.isValidSync(true)).toBe(true);
  expect(yupSchema.isValidSync(false)).toBe(true);
});

test('typeError expect fail message', async () => {
  const [error] = await to(yupSchema.validate('5'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
