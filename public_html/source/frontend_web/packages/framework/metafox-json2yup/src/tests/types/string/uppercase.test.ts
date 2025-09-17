import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Must be uppercase';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  case: 'uppercase',
  errors: {
    uppercase: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('uppercase expect fail', async () => {
  expect(yupSchema.isValidSync('HELLo')).toBe(false);
  expect(yupSchema.isValidSync('HeL3Lo')).toBe(false);
  expect(yupSchema.isValidSync(123)).toBe(false);
  expect(yupSchema.isValidSync(['HELLO'])).toBe(false);
  expect(yupSchema.isValidSync({ HELLO: 'HELLO' })).toBe(false);
});

test('uppercase expect pass', async () => {
  expect(yupSchema.isValidSync('HELLO')).toBe(true);
  expect(yupSchema.isValidSync('HELLO   HELLO')).toBe(true);
});

test('uppercase expect fail message', async () => {
  const [error] = await to(yupSchema.validate('HELLO HellO'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
