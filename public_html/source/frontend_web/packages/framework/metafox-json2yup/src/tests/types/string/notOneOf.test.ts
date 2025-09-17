import to from 'await-to-js';
import { StringSchema, ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const errorMsg = 'Can\'t be "one" or "two"';
const schema: StringTypeSchema = {
  type: 'string',
  strict: true,
  notOneOf: ['one', 'two'],
  errors: {
    notOneOf: errorMsg
  }
};
const yupSchema = toYup(schema) as StringSchema;

test('notOneOf expect fail', async () => {
  expect(yupSchema.isValidSync('one')).toBe(false);
  expect(yupSchema.isValidSync('two')).toBe(false);
});

test('notOneOf expect pass', async () => {
  expect(yupSchema.isValidSync('One')).toBe(true);
  expect(yupSchema.isValidSync('twO')).toBe(true);
  expect(yupSchema.isValidSync('three')).toBe(true);
  expect(yupSchema.isValidSync('four')).toBe(true);
});

test('notOneOf expect fail message', async () => {
  const [error] = await to(yupSchema.validate('one'));
  expect((error as ValidationError).message).toBe(errorMsg);
});
