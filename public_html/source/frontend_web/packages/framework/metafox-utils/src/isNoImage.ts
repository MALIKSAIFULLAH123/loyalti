export default function isNoImage(src: string | undefined): boolean {
  return src ? /no_image\.\w+/.test(src) : true;
}
