import DialogBackend from './DialogBackend';

describe('DialogBackend', () => {
  const backend = new DialogBackend({});

  it('+constructor', () => {
    expect(backend).toBeDefined();
    const onChanged = jest.fn();
    const token = backend.subscribe(onChanged);

    backend.dismiss();

    expect(onChanged).toBeCalledTimes(0);

    backend.alert({ title: 'Alert!', message: 'This is a dialog message' });

    expect(onChanged).toBeCalledTimes(1);

    backend.confirm({ title: 'Confirm!', message: 'This is dialog message' });

    expect(onChanged).toBeCalledTimes(2);

    backend.unsubscribe(token);

    backend.present({ component: 'DialogComponent', props: {} });

    backend.comingSoon();

    expect(onChanged).toBeCalledTimes(2);

    backend.dismiss();
    expect(onChanged).toBeCalledTimes(2);
    backend.dismiss(true);
    expect(onChanged).toBeCalledTimes(2);
  });

  it('+subscribe', () => {
    const backend = new DialogBackend({});
    expect(backend).toBeDefined();
  });
});
