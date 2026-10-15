# Interface Design Rules

## Responsive Cards

- Use the same responsive grid behavior for repeated product-card sections across the public website. On narrow phone screens, show two cards per row; use four columns on wide desktop layouts when the available content width supports them.
- Keep card columns equal-width with `min-w-0`, consistent gaps, and matching image aspect ratios so cards align across sections.
- Keep card heights and internal spacing consistent within each section. Let names and descriptions wrap or clamp without overflowing; keep prices and primary actions visible and aligned near the bottom.
- Reduce card padding, text size, and minimum height at phone widths so two-column cards remain legible and avoid excessive empty space.
- Do not force two columns for content that is not a repeated product-card grid, or when accessibility, a complex workflow, or genuinely narrow available space requires a single-column layout. Prefer a breakpoint-aware layout over horizontal overflow.

## Shared Visual Language

- Reuse the established brand colors, border radii, typography, image treatment, button styles, and hover/focus states. Do not create a separate visual style for a new card section.
- Keep interactive controls touch-friendly, keyboard accessible, and visually distinguishable; preserve clear focus indicators and sufficient text contrast.
- Verify card grids at phone, tablet, and desktop widths after changing their layout.
