export default function stripTags(text: string): string {
  return text.replace(/<\/?[^>]+(>|$)/g, '');
}
