/**
 * @type: service
 * name: formRefs
 */
import { FormikProps } from 'formik';

export default class FormRefs {
  private refs = {};

  public bootstrap() {}

  public tag(name, ref): void {
    this.refs[name] = ref;
  }

  public get<T = any>(name): FormikProps<T> {
    return this.refs[name];
  }
}
