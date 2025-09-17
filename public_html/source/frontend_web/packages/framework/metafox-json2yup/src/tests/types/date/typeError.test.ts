import to from 'await-to-js';
import { DateSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const errorMsg = 'Has to be a date';
const schema: DateTypeSchema = {
  type: 'date',
  strict: true,
  required: true,
  errors: {
    typeError: errorMsg
  }
};
const yupSchema = toYup(schema) as DateSchema;

test('typeError expect fail', async () => {
  expect(yupSchema.isValidSync(5)).toBe(false);
  expect(yupSchema.isValidSync('5')).toBe(false);
  expect(yupSchema.isValidSync([])).toBe(false);
  expect(yupSchema.isValidSync({})).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync(undefined)).toBe(false);
  expect(yupSchema.isValidSync(true)).toBe(false);
  expect(yupSchema.isValidSync(false)).toBe(false);
});

test('typeError expect pass', async () => {
  expect(yupSchema.isValidSync(new Date())).toBe(true);
});

test('typeError expect fail message', async () => {
  const [error] = await to(yupSchema.validate(null));
  expect((error as ValidationError).message).toBe(errorMsg);
});
