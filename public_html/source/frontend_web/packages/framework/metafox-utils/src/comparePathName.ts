export default function comparePathName(pathA: string, pathB: string) {
  let result = false;

  const regex = /^\/|\/$/g;

  if (pathA && pathB) {
    result = pathA.replace(regex, '') === pathB.replace(regex, '');
  }

  return result;
}
