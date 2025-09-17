import { DateSchema } from 'yup';
import { toYup } from '../../../toYup';
import { DateTypeSchema } from '../../../types';

const schema: DateTypeSchema = {
  type: 'date',
  required: true,
  nullable: false,
  inputFormat: 'yyyy-MM-dd'
};

const yupSchema = toYup(schema) as DateSchema;

test('required expect fail', async () => {
  expect(yupSchema.isValidSync(undefined)).toBe(false);
  expect(yupSchema.isValidSync(null)).toBe(false);
  expect(yupSchema.isValidSync('01/01/2018')).toBe(false); // doesn't match the input format
  expect(yupSchema.isValidSync('2018-18-01')).toBe(false); // completely invalid date for the format
});

test('required expect pass', async () => {
  expect(yupSchema.isValidSync('2018-01-01')).toBe(true);
  expect(
    toYup({
      type: 'date',
      required: true,
      nullable: false,
      inputFormat: 'dd/MM/yyyy'
    } as DateTypeSchema).isValidSync('01/01/2018')
  ).toBe(true);
  expect(
    toYup({
      type: 'date',
      required: true,
      nullable: false,
      inputFormat: 'MM/yyyy'
    } as DateTypeSchema).isValidSync('01/2018')
  ).toBe(true);
  expect(
    toYup({
      type: 'date',
      required: true,
      nullable: false,
      inputFormat: 'MM-yyyy'
    } as DateTypeSchema).isValidSync('01-2018')
  ).toBe(true);
});
