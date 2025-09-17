import { StringSchema } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const schema: StringTypeSchema = {
  type: 'string',
  strict: true
};
const yupSchema = toYup(schema) as StringSchema;

test('strict string type', async () => {
  expect(yupSchema.isValidSync('hello')).toBe(true);
});

test('strict string type - number value', async () => {
  expect(yupSchema.isValidSync(1)).toBe(false);
});

test('strict string type - boolean value', async () => {
  expect(yupSchema.isValidSync(true)).toBe(false);
  expect(yupSchema.isValidSync(false)).toBe(false);
});

test('strict string type - array value', async () => {
  expect(yupSchema.isValidSync([])).toBe(false);
});

test('strict string type - object value', async () => {
  expect(yupSchema.isValidSync({})).toBe(false);
});
