export default function toArrayCategory(data: any) {
  const array = [];
  let temp = data?.category;

  if (temp) array.push(temp);

  while (temp?.parentCategory) {
    temp = temp?.parentCategory;
    array.unshift(temp);
  }

  return array;
}