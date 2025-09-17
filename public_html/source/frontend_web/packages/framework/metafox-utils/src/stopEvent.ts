export default function stopEvent(fn: () => void) {
  return (evt: any) => {
    if (evt) {
      evt.preventDefault();
    }

    fn();
  };
}
