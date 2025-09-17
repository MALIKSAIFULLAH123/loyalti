import to from 'await-to-js';
import { ValidationError } from 'yup';
import { toYup } from '../../../toYup';
import { ObjectTypeSchema } from '../../../types';

const schema: ObjectTypeSchema = {
  type: 'object',
  strict: true,
  properties: {
    allowedToBuy: {
      type: 'boolean',
      strict: true,
      required: true
    },
    buy: {
      type: 'boolean',
      strict: true,
      required: true,
      when: [
        {
          fields: 'allowedToBuy',
          is: true,
          then: {
            type: 'boolean',
            oneOf: [true],
            errors: {
              oneOf: 'Must buy allowedToBuy is true'
            }
          },
          otherwise: {
            type: 'boolean',
            oneOf: [false],
            errors: {
              oneOf: 'Cannot not buy allowedToBuy is true'
            }
          }
        }
      ]
    }
  }
};

const yupSchema = toYup(schema) as any;

test('when number expect fail', async () => {
  expect(
    yupSchema.isValidSync({
      allowedToBuy: true,
      buy: false
    })
  ).toBe(false);

  expect(
    yupSchema.isValidSync({
      allowedToBuy: false,
      buy: true
    })
  ).toBe(false);
});

test('when number expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      allowedToBuy: true,
      buy: true
    })
  ).toBe(true);
  expect(
    yupSchema.isValidSync({
      allowedToBuy: false,
      buy: false
    })
  ).toBe(true);
});

test('when boolean allowedToBuy true expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      allowedToBuy: true,
      buy: false
    })
  );
  const yupError = error as ValidationError;
  expect(yupError.errors).toContain('Must buy allowedToBuy is true');
});

test('when boolean allowedToBuy false expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      allowedToBuy: false,
      buy: true
    })
  );
  const yupError = error as ValidationError;
  expect(yupError.errors).toContain('Cannot not buy allowedToBuy is true');
});
