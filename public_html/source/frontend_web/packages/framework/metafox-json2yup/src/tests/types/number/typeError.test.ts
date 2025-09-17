import to from 'await-to-js';
import { NumberSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { NumberTypeSchema } from '../../../types';

const errorMsg = 'Has to be a number';
const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  required: true,
  errors: {
    typeError: errorMsg
  }
};
const yupSchema = toYup(schema) as NumberSchema;

test('typeError expect fail', async () => {
  expect(yupSchema.isValidSync('5')).toBe(false);
  expect(yupSchema.isValidSync(false)).toBe(false);
  expect(yupSchema.isValidSync([])).toBe(false);
  expect(yupSchema.isValidSync({})).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
});

test('typeError expect pass', async () => {
  expect(yupSchema.isValidSync(5)).toBe(true);
});

test('typeError expect fail message', async () => {
  const [error] = await to(yupSchema.validate('5'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
