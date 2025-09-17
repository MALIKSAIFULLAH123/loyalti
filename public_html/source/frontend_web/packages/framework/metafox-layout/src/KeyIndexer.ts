/**
 * This class help for index blocks in the same slot by component and
 * the indexing name.
 * be careful about render-performance when you modify this class.
 */
export class KeyIndexer {
  public data = {};

  public forBlock(slot: string, component: string): string {
    const key = `${slot}:${component}`;

    if (!this.data[key]) {
      this.data[key] = 1;
    } else {
      this.data[key]++;
    }

    return `${component}:${this.data[key]}`;
  }
}

export default KeyIndexer;
