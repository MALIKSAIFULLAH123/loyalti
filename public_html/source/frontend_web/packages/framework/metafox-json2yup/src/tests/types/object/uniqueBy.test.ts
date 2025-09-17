import { toYup } from '../../../toYup';

describe('object.uniqueBy', () => {
  const schemaNotNullable = {
    type: 'array',
    strict: true,
    nullable: false,
    of: {
      type: 'object',
      uniqueBy: 'name',
      properties: {
        name: { type: 'string' }
      }
    }
  };

  const json = toYup(schemaNotNullable as any);

  test('uniqueBy', async () => {
    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'an', id: 2 }
      ])
    ).toBe(false);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'an', id: 2 }
      ])
    ).toBe(false);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: '', id: 2 },
        { name: '', id: 3 }
      ])
    ).toBe(true);

    expect(
      json.isValidSync([
        { name: 'an', id: 1 },
        { name: 'ba', id: 2 }
      ])
    ).toBe(true);
  });
});
