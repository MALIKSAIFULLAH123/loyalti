import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Email address is not valid';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  format: 'email',
  errors: {
    email: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('email expect fail', async () => {
  expect(yupSchema.isValidSync('whats')).toBe(false);
  expect(yupSchema.isValidSync('asd@example')).toBe(false);
  expect(yupSchema.isValidSync('asd+132@a')).toBe(false);
  expect(yupSchema.isValidSync(['asdad'])).toBe(false);
  expect(yupSchema.isValidSync(12334)).toBe(false);
});

test('email expect pass', async () => {
  expect(yupSchema.isValidSync('something@example.com')).toBe(true);
  expect(yupSchema.isValidSync('something+123@example.com')).toBe(true);
  expect(yupSchema.isValidSync('some_thing+123@example.com')).toBe(true);
  expect(yupSchema.isValidSync('some_thing+hello@example.com')).toBe(true);
  expect(yupSchema.isValidSync('some.thing+hello@example.com')).toBe(true);
  expect(yupSchema.isValidSync('some.thing+123@example.com')).toBe(true);
  expect(yupSchema.isValidSync('some.thing@example.com')).toBe(true);
});

test('email expect fail message', async () => {
  const [error] = await to(yupSchema.validate('123'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
