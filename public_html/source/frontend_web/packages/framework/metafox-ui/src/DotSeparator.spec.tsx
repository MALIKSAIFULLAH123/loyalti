import { render } from '@testing-library/react';
import * as React from 'react';
import DotSeparator from './DotSeparator';

const MockChildComponent = ({ index }) => <>item {index}</>;

describe('DotSeparator', () => {
  const props = {
    sx: {
      color: 'text.secondary',
      mt: 1
    }
  };

  it('render empty item', async () => {
    const { container } = render(<DotSeparator {...props} />);

    expect(container).toMatchSnapshot();
  });

  it('render have item', async () => {
    const { container } = render(
      <DotSeparator {...props}>
        <MockChildComponent index={1} />
        <MockChildComponent index={2} />
      </DotSeparator>
    );

    expect(container).toMatchSnapshot();
  });
});
