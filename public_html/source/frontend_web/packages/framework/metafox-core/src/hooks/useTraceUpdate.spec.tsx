import nextId from '@metafox/utils/nextId';
import { fireEvent, render, waitFor } from '@testing-library/react';
import * as React from 'react';
import useTraceUpdate from './useTraceUpdate';

function MockComponent({ data }: any) {
  useTraceUpdate('mock', data);

  return <div data-testid="myComponent" />;
}

function ParentObjectComponent() {
  const [data, setData] = React.useState<{ x: number }>({ x: 2 });

  const onClick = React.useCallback(() => {
    setData(prev => ({ ...prev, x: prev.x++ }));
  }, []);

  return (
    <div>
      <MockComponent data={data} />
      <button data-testid="button" onClick={onClick} />
    </div>
  );
}

function ParentArrayComponent({ initial }) {
  const [data, setData] = React.useState<string[]>(initial);

  const onClick = React.useCallback(() => {
    setData(prev => [...(prev ? prev : []), nextId()]);
  }, []);

  return (
    <div>
      <MockComponent data={data} />
      <button data-testid="button" onClick={onClick} />
    </div>
  );
}

describe('useTraceUpdate', () => {
  it('+object', async () => {
    const { getByTestId } = render(<ParentObjectComponent />);

    let info = undefined;
    const spy = jest
      .spyOn(global.console, 'info')
      .mockImplementation(message => {
        info = message;
      });

    await waitFor(() => {
      expect(getByTestId('button')).toBeInTheDocument();
      expect(getByTestId('myComponent')).toBeInTheDocument();

      fireEvent.click(getByTestId('button'));
      expect(info).toEqual('mock updated:');
    });
    spy.mockRestore();
  });
  it('+array', async () => {
    const { getByTestId } = render(
      <ParentArrayComponent initial={undefined} />
    );

    let info = undefined;
    const spy = jest
      .spyOn(global.console, 'info')
      .mockImplementation(message => {
        info = message;
      });

    await waitFor(() => {
      expect(getByTestId('button')).toBeInTheDocument();
      expect(getByTestId('myComponent')).toBeInTheDocument();

      fireEvent.click(getByTestId('button'));
      expect(info).toEqual('mock updated:');
    });
    spy.mockRestore();
  });
});
