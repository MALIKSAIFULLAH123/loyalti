import '@metafox/framework/Manager';

declare module '@metafox/framework/Manager' {
  interface GlobalState {}

  interface AppResource {
    forms?: Partial<Record<FormName, FormSchemaShape>>;
  }

  interface Manager {}
}
