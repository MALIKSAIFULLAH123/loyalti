import { toYup } from '../../../toYup';

const schema: any = {
  type: 'object',
  properties: {
    core: {
      properties: {
        services: {
          properties: {
            ses: {
              type: 'object',
              properties: {
                key: {
                  type: 'string',
                  errors: {
                    required: '${path} is a required field.'
                  },
                  required: true,
                  label: 'SES Key'
                },
                secret: {
                  type: 'string',
                  errors: {
                    required: '${path} is a required field.'
                  },
                  required: true,
                  label: 'SES Secret'
                },
                region: {
                  type: 'string',
                  errors: {
                    required: '${path} is a required field.'
                  },
                  required: true,
                  label: 'Region'
                }
              }
            }
          }
        }
      }
    }
  }
};

const yupSchema = toYup(schema);

test('required expect fail', async () => {
  expect(await yupSchema.isValidSync({})).toBe(false);
});
