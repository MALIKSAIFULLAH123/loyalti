import { StringSchema } from 'yup';
import { toYup } from '../../../toYup';
import { StringTypeSchema } from '../../../types';

const schemaNullable: StringTypeSchema = {
  type: 'string',
  strict: true,
  nullable: true
};

const schemaNotNullable: StringTypeSchema = {
  type: 'string',
  strict: true,
  nullable: false
};

const yupNullableSchema = toYup(schemaNullable) as StringSchema;
const yupNotNullableSchema = toYup(schemaNotNullable) as StringSchema;

test('nullable expect fail', async () => {
  expect(yupNullableSchema.isValidSync([])).toBe(false);
  expect(yupNullableSchema.isValidSync({})).toBe(false);
  expect(yupNullableSchema.isValidSync(123)).toBe(false);
});

test('nullable expect pass', async () => {
  expect(yupNullableSchema.isValidSync('hello')).toBe(true);
  expect(yupNullableSchema.isValidSync(null)).toBe(true);
  expect(yupNullableSchema.isValidSync(undefined)).toBe(true);
});

test('not nullable expect fail', async () => {
  expect(yupNotNullableSchema.isValidSync(null)).toBe(false);
  expect(yupNotNullableSchema.isValidSync([])).toBe(false);
  expect(yupNotNullableSchema.isValidSync({})).toBe(false);
  expect(yupNotNullableSchema.isValidSync(123)).toBe(false);
});

test('not nullable expect pass', async () => {
  expect(yupNotNullableSchema.isValidSync('hello')).toBe(true);
});
