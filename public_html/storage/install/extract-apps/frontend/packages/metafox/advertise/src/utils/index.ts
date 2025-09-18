function isNumber(n: any) {
  return /^-?[\d.]+(?:e-?\d+)?$/.test(n);
}

export function commify(n: any) {
  if (!isNumber(n)) {
    return 'N/A';
  }

  const parts = n.toString().split('.');

  const numberPart = parts[0];
  const decimalPart = parts[1];
  const thousands = /\B(?=(\d{3})+(?!\d))/g;

  return (
    numberPart.replace(thousands, ',') + (decimalPart ? `.${decimalPart}` : '')
  );
}
