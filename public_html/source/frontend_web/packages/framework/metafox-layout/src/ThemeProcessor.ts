/**
 * @type: service
 * name: themeProcessor
 */

import { Theme } from '@metafox/ui/styles';
import { isArray } from 'lodash';

type Processor = (theme: Theme) => void;

export default class ThemeProcessor {
  private processors: Processor[] = [];

  public bootstrap() {}

  public setProcessors(processors: Processor[]) {
    this.processors = processors;
  }

  public process(theme: Theme): void {
    if (isArray(this.processors)) {
      this.processors.forEach(process => process(theme));
    }
  }
}
