import * as React from 'react';
import renderer from 'react-test-renderer';
import BlockLoadingComponent from './BlockLoadingComponent';

describe('BlockLoadingComponent', () => {
  it('render no err', async () => {
    const loading = renderer.create(<BlockLoadingComponent />);
    expect(loading.toJSON()).toMatchSnapshot();
  });
  it('render err', async () => {
    const loading = renderer.create(
      <BlockLoadingComponent error="error server" />
    );
    expect(loading.toJSON()).toMatchSnapshot();
  });
});
