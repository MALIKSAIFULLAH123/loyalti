import inspectDotProps from './inspectDotProps';

describe('inspectDotProps', () => {
  it('inspectDotProps with parent key', () => {
    expect(
      Object.keys({
        name: 'jacky',
        email: 'jacky@example.com',
        password: 'etc',
        onClick: () => void 0
      })
    ).toEqual(['name', 'email', 'password', 'onClick']);
  });

  it('inspectDotProps with parent key', () => {
    const props = {};

    inspectDotProps(
      {
        user: {
          name: 'jacky',
          email: 'jacky@example.com',
          password: 'etc',
          onClick: () => void 0
        }
      },
      true,
      props,
      'item'
    );

    expect(props).toEqual({
      'item.user.email': true,
      'item.user.name': true,
      'item.user.password': true,
      'item.user.onClick': true
    });
  });

  it('inspectDotProps without parent key', () => {
    const props = {};

    inspectDotProps(
      {
        user: { name: 'jacky', email: 'jacky@example.com', password: 'etc' }
      },
      true,
      props,
      undefined
    );

    expect(props).toEqual({
      'user.email': true,
      'user.name': true,
      'user.password': true
    });
  });
});
