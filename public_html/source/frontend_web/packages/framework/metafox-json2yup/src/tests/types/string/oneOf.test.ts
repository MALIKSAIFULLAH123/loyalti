import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Must be either "one" or "two"';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  oneOf: ['one', 'two'],
  errors: {
    oneOf: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('oneOf expect fail', async () => {
  expect(yupSchema.isValidSync('hello')).toBe(false);
  expect(yupSchema.isValidSync('One')).toBe(false);
  expect(yupSchema.isValidSync('twO')).toBe(false);
});

test('oneOf expect pass', async () => {
  expect(yupSchema.isValidSync('one')).toBe(true);
  expect(yupSchema.isValidSync('two')).toBe(true);
});

test('oneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate('hello'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
