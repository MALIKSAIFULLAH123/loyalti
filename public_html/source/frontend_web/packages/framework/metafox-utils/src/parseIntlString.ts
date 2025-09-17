const regex = /\[(?<name>[\w]+):(?<value>[^\]]+)\]/gm;

export default function parseIntlString(str: string): {
  props: Record<string, string>;
  child: string;
} {
  let array: RegExpExecArray;
  const props: Record<string, string> = {};

  while ((array = regex.exec(str)) !== null) {
    if (array.index === regex.lastIndex) {
      regex.lastIndex++;
    }

    props[array.groups.name] = array.groups.value;
  }

  return {
    props,
    child: str.replace(regex, '')
  };
}
