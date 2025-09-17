import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Email address is not valid';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  format: 'url',
  errors: {
    url: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('url expect fail', async () => {
  expect(yupSchema.isValidSync('whats')).toBe(false);
  expect(yupSchema.isValidSync('asd@example')).toBe(false);
  expect(yupSchema.isValidSync('asd+132@a')).toBe(false);
  expect(yupSchema.isValidSync(['asdad'])).toBe(false);
  expect(yupSchema.isValidSync(12334)).toBe(false);
});

test('url expect pass', async () => {
  expect(yupSchema.isValidSync('http://something.example.com')).toBe(true);
  expect(yupSchema.isValidSync('http://example.com')).toBe(true);
  expect(yupSchema.isValidSync('http://example.com/some/path')).toBe(true);
  expect(
    yupSchema.isValidSync(
      'http://example.com/some/path?with=query&string=value'
    )
  ).toBe(true);
});

test('url expect fail message', async () => {
  const [error] = await to(yupSchema.validate('123'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
