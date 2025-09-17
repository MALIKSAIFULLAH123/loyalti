const roundedFileSize = (value: number) => Math.round(value * 10) / 10;

export default function parseFileSize(value: number) {
  if (value === 0) return 0;

  // value is unit byte
  const sizeMb = value / 1024 / 1024;

  if (sizeMb < 1) {
    return `${Math.ceil(value / 1024)} KB`;
  }

  return `${roundedFileSize(sizeMb)} MB`;
}
