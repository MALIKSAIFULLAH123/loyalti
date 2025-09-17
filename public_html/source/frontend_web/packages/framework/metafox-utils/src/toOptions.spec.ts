import toOptions from './toOptions';

describe('convert array to options list', () => {
  it('without overrides', () => {
    const options = [
      { label: 'Business', value: 1 },
      { label: 'Education', value: 2 },
      { label: 'Entertainment', value: 3 },
      { label: 'Family & Home', value: 4 },
      { label: 'Health', value: 5 },
      { label: 'Recreation', value: 6 },
      { label: 'Shopping', value: 7 },
      { label: 'Society', value: 8 },
      { label: 'Sports', value: 9 },
      { label: 'Technology', value: 10 }
    ];

    const subOptions = {
      '1': [
        { label: 'Business 1', value: 11 },
        { label: 'Business 2', value: 12 }
      ]
    };

    const dest = toOptions(options, subOptions, true, 'options', '-');

    expect(dest.length).toEqual(12);
  });
});
