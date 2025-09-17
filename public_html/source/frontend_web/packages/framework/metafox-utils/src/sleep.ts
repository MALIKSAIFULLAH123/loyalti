export default function sleep(milisecond: number): Promise<void> {
  return new Promise<void>(resolve => setTimeout(resolve, milisecond));
}
