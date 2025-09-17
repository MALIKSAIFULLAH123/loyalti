import '@metafox/framework/Manager';
import FormRefs from '@metafox/form/services/FormRefs';

declare module '@metafox/framework/Manager' {
  interface GlobalState {}

  interface Manager {
    formRefs?: FormRefs;
  }
}
