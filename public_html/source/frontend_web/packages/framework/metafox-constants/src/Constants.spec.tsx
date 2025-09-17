import Constants from './Constants';

describe('Constants', () => {
  it('Test Constructor', () => {
    const constant = new Constants();

    expect(constant).toBeDefined();

    const deps = jest.fn();
    const get = jest.fn();
    const manager = {
      deps,
      cookieBackend: { get }
    };

    expect(constant.bootstrap(manager as any));

    expect(deps).toBeCalledTimes(1);
    expect(get).toBeCalledTimes(1);

    expect(constant.isLayoutPreviewWindow).toBeFalsy();
    expect(constant.previewDevice).toEqual('');
  });
});
