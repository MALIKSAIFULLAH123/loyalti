import * as React from 'react';
import renderer from 'react-test-renderer';
import Loading from './index';

describe('Loading', () => {
  it('<Loading />', async () => {
    const loading = renderer.create(<Loading />).toJSON();
    expect(loading).toMatchSnapshot();
  });

  it('<Loading related/>', async () => {
    const loading = renderer.create(<Loading related />).toJSON();
    expect(loading).toMatchSnapshot();
  });

  it('<Loading center/>', async () => {
    const loading = renderer.create(<Loading center />).toJSON();
    expect(loading).toMatchSnapshot();
  });

  it('<Loading related center/>', async () => {
    const loading = renderer.create(<Loading related center />).toJSON();
    expect(loading).toMatchSnapshot();
  });

  it('<Loading darkMode/>', async () => {
    window.matchMedia = jest.fn().mockReturnValue({ matches: 1 });
    const loading = renderer.create(<Loading />).toJSON();
    expect(loading).toMatchSnapshot();
  });
});
