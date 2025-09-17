import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Must be lowercase';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  case: 'lowercase',
  errors: {
    lowercase: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('lowercase expect fail', async () => {
  expect(yupSchema.isValidSync('HELLo')).toBe(false);
  expect(yupSchema.isValidSync('HeL3Lo')).toBe(false);
  expect(yupSchema.isValidSync(123)).toBe(false);
  expect(yupSchema.isValidSync(['HELLO'])).toBe(false);
  expect(yupSchema.isValidSync({ HELLO: 'HELLO' })).toBe(false);
});

test('lowercase expect pass', async () => {
  expect(yupSchema.isValidSync('hello')).toBe(true);
  expect(yupSchema.isValidSync('hello   hello')).toBe(true);
});

test('lowercase expect fail message', async () => {
  const [error] = await to(yupSchema.validate('hello hellO'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
