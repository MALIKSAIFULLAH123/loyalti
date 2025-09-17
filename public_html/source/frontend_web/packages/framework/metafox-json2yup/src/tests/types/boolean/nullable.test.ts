import { BooleanSchema } from 'yup';
import { toYup } from '../../../toYup';
import { BooleanTypeSchema } from '../../../types';

const schemaNullable: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  nullable: true
};

const schemaNotNullable: BooleanTypeSchema = {
  type: 'boolean',
  strict: true,
  nullable: false
};

const yupNullableSchema = toYup(schemaNullable) as BooleanSchema;
const yupNotNullableSchema = toYup(schemaNotNullable) as BooleanSchema;

test('nullable expect fail', async () => {
  expect(yupNullableSchema.isValidSync(123)).toBe(false);
  expect(yupNullableSchema.isValidSync([])).toBe(false);
  expect(yupNullableSchema.isValidSync({})).toBe(false);
  expect(yupNullableSchema.isValidSync('hello')).toBe(false);
});

test('nullable expect pass', async () => {
  expect(yupNullableSchema.isValidSync(true)).toBe(true);
  expect(yupNullableSchema.isValidSync(false)).toBe(true);
  expect(yupNullableSchema.isValidSync(null)).toBe(true);
  expect(yupNullableSchema.isValidSync(undefined)).toBe(true);
});

test('not nullable expect fail', async () => {
  expect(yupNotNullableSchema.isValidSync(null)).toBe(false);
  expect(yupNotNullableSchema.isValidSync([])).toBe(false);
  expect(yupNotNullableSchema.isValidSync({})).toBe(false);
  expect(yupNotNullableSchema.isValidSync('sup')).toBe(false);
  expect(yupNotNullableSchema.isValidSync(123)).toBe(false);
});

test('not nullable expect pass', async () => {
  expect(yupNotNullableSchema.isValidSync(true)).toBe(true);
  expect(yupNotNullableSchema.isValidSync(false)).toBe(true);
});
