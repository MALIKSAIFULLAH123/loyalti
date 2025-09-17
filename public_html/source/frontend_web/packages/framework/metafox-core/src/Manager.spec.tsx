import { Manager } from './index';

class ServiceA {
  options: any;
  constructor(options: Record<string, any>) {
    this.options = options;
  }
  bootstrap() {}
}

class ServiceB {
  options: any;

  constructor(options: any) {
    this.options = options;
  }

  public bootstrap(manager: Manager) {
    manager.deps('serviceC', 'serviceA');

    return { data: { sampleValue: '2' } };
  }
}

class ServiceC {
  options: any;
  constructor(options: Record<string, any>) {
    this.options = options;
  }
  bootstrap() {}
}

describe('@metafox/framework/Manager', () => {
  it('Manager', () => {
    const manager = Manager.factory({});

    manager.use({
      singleObject: { fnA: () => 'fnA result' },
      singleFunction: () => 'singleFunction result',
      singleArray: ['value1', 'value2']
    });

    expect(manager).toBeDefined();
    expect(manager.singleObject.fnA()).toEqual('fnA result');
    expect(manager.singleFunction()).toEqual('singleFunction result');
    expect(manager.singleArray).toEqual(['value1', 'value2']);
  });
  it('Manager.use()', () => {
    const manager = Manager.factory({});

    manager.use({
      firstFunction: () => 'function 1',
      firstValue: 'value 1'
    });

    expect(manager.firstFunction()).toEqual('function 1');
    expect(manager.firstValue).toEqual('value 1');

    manager.use({
      firstFunction: () => 'function 2',
      firstValue: 'value 2',
      secondValue: 'value 3'
    });

    expect(manager.firstFunction()).toEqual('function 2');
    expect(manager.firstValue).toEqual('value 2');
    expect(manager.secondValue).toEqual('value 3');
  });

  it('Manager.use() with params', () => {
    const manager = Manager.factory({ serviceA: { mockValue: 'dark' } });

    manager.use({
      serviceA: ServiceA
    });

    const backend = manager.serviceA;

    expect(backend).toBeDefined();

    expect(backend.options.mockValue).toEqual('dark');
  });

  it('Manager.use() unexpeted Name', () => {
    const manager = Manager.factory({});

    expect(() => {
      manager.use({ keepServices: ServiceC });
    }).toThrow(/Duplicated preserved name /i);

    expect(() => {
      manager.use({ allConfig: ServiceC });
    }).toThrow(/Duplicated preserved name /i);

    expect(() => {
      manager.use({ use: ServiceC });
    }).toThrow(/Duplicated preserved name /i);

    expect(() => {
      manager.use({ make: ServiceC });
    }).toThrow(/Duplicated preserved name /i);

    expect(() => {
      manager.use({ bootstrap: ServiceC });
    }).toThrow(/Duplicated preserved name /i);
  });

  it('Manager.use() with dependencies', () => {
    const manager = Manager.factory({});

    manager.use({
      serviceA: ServiceA,
      serviceB: ServiceB,
      serviceC: ServiceC
    });

    expect(manager.serviceA).toBeDefined();
    expect(manager.serviceB).toBeDefined();
    expect(manager.serviceC).toBeDefined();

    // try reinitialize
  });
});
