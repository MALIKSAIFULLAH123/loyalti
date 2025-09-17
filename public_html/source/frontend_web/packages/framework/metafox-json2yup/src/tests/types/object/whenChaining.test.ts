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
    sweets: {
      type: 'number',
      strict: true,
      required: true,
      when: [
        {
          fields: 'allowedToBuy',
          is: true,
          then: {
            type: 'number',
            min: 1,
            errors: {
              min: 'Must buy at least 1 when allowedToBuy is true'
            }
          }
        },
        {
          fields: 'allowedToBuy',
          is: false,
          then: {
            type: 'number',
            max: 0,
            errors: {
              max: "Can't buy any when allowedToBuy is false"
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
      sweets: 0
    })
  ).toBe(false);

  expect(
    yupSchema.isValidSync({
      allowedToBuy: false,
      sweets: 1
    })
  ).toBe(false);
});

test('when number expect pass', async () => {
  expect(
    yupSchema.isValidSync({
      allowedToBuy: true,
      sweets: 5
    })
  ).toBe(true);
  expect(
    yupSchema.isValidSync({
      allowedToBuy: false,
      sweets: 0
    })
  ).toBe(true);
});

test('when number allowedToBuy true expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      allowedToBuy: true,
      sweets: 0
    })
  );
  const yupError = error as ValidationError;

  expect(yupError.errors).toContain(
    'Must buy at least 1 when allowedToBuy is true'
  );
});

test('when number allowedToBuy false expect errors', async () => {
  const [error] = await to(
    yupSchema.validate({
      allowedToBuy: false,
      sweets: 1
    })
  );
  const yupError = error as ValidationError;
  expect(yupError.errors).toContain("Can't buy any when allowedToBuy is false");
});
