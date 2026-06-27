export function firstValidationMessage(errors: Record<string, string[]>, field: string): string | undefined {
  return errors[field]?.[0];
}

export function compactNote(parts: (string | null | undefined)[], maxLength = 1000): string | null {
  const note = parts
    .map((part) => part?.trim())
    .filter((part): part is string => Boolean(part))
    .join('\n\n');

  if (!note) {
    return null;
  }

  return note.length > maxLength ? `${note.slice(0, maxLength - 3)}...` : note;
}
