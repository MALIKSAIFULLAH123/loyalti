import { StringSchema } from 'yup';
import { toYup } from '../../../toYup';

const schema = {
  type: 'object',
  properties: {
    min_password_length: {
      type: 'number',
      required: true
    },
    password: {
      type: 'string',
      minLength: { ref: 'min_password_length' }
    }
  }
};
const yupSchema = toYup(schema as any) as StringSchema;

test('password expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      min_password_length: 3,
      password: 'ab'
    })
  ).toBe(false);
  expect(
    yupSchema.isValidSync({
      min_password_length: 6,
      password: 'abc'
    })
  ).toBe(false);
});

test('password expect true', async () => {
  expect(
    yupSchema.isValidSync({
      min_password_length: 3,
      password: 'abc'
    })
  ).toBe(true);

  expect(
    yupSchema.isValidSync({
      min_password_length: 6,
      password: 'abcdefgh'
    })
  ).toBe(true);
});
