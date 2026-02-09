# Design Inventory (Baseline)

Collected from:
- tailwind.config.js
- resources/css/app.css
- resources/css/sidebar.css
- resources/css/pages/academic-year.css
- resources/views/** (inline styles and SVG fills)

## Existing CSS variables (colors)
- --color-primary: 99 102 241
- --color-secondary: 100 116 139
- --color-accent: 236 72 153
- --color-success: 34 197 94
- --color-warning: 234 179 8
- --color-danger: 239 68 68
- --color-info: 59 130 246
- --color-surface: 255 255 255
- --color-white: 255 255 255
- --color-black: 0 0 0
- --color-background: 248 250 252
- --color-text-primary: 15 23 42
- --color-text-secondary: 71 85 105
- --color-foreground: 15 23 42
- --color-muted-foreground: 71 85 105
- --color-border: 226 232 240
- --scrollbar-track: 148 163 184

## Sidebar tokens
- --sidebar-link: 203 213 225
- --sidebar-link-hover: 241 245 249
- --sidebar-link-active: 255 255 255
- --sidebar-submenu-link: 148 163 184
- --sidebar-submenu-link-hover: 226 232 240
- --sidebar-divider: 100 116 139
- --sidebar-border: 148 163 184
- --sidebar-header-bg: 99 102 241
- --sidebar-footer-bg: 0 0 0
- --sidebar-overlay: 0 0 0
- --sidebar-scrollbar: 99 102 241
- --sidebar-badge-start: 239 68 68
- --sidebar-badge-end: 220 38 38
- --sidebar-active-shadow: 99 102 241
- --sidebar-active-gradient-start: 99 102 241
- --sidebar-active-gradient-end: 139 92 246

## Form tokens
- --form-input-border: 203 213 225
- --form-label: 51 65 85
- --form-select-border: 209 213 219

## Modal tokens
- --modal-overlay: 107 114 128
- --modal-overlay-dark: 17 24 39
- --modal-surface-dark: 31 41 55
- --modal-title: 17 24 39
- --modal-title-dark: 243 244 246
- --modal-text: 75 85 99
- --modal-text-dark: 156 163 175
- --modal-footer: 243 244 246

## Chart tokens
- --chart-title: 51 65 85
- --chart-center: 17 24 39

## Print tokens
- --print-border: 51 51 51
- --print-muted: 102 102 102
- --print-table-header: 240 240 240
- --print-button: 37 99 235
- --print-button-hover: 29 78 216

## Error tokens
- --error-text: 99 107 111

## Existing CSS variables (gradients)
- --gradient-purple-blue-start: 147 51 234
- --gradient-purple-blue-end: 37 99 235
- --gradient-green-teal-start: 34 197 94
- --gradient-green-teal-end: 20 184 166
- --gradient-orange-pink-start: 249 115 22
- --gradient-orange-pink-end: 236 72 153
- --gradient-red-purple-start: 239 68 68
- --gradient-red-purple-end: 168 85 247
- --gradient-dark-gold-start: 30 41 59
- --gradient-dark-gold-end: 15 23 42
- --gradient-gold-amber-start: 251 191 36
- --gradient-gold-amber-end: 245 158 11
- --gradient-midnight-start: 15 23 42
- --gradient-midnight-end: 30 58 138

## Tailwind gradient names (raw values)
- gradient-purple-blue: rgb(147, 51, 234) -> rgb(37, 99, 235)
- gradient-green-teal: rgb(34, 197, 94) -> rgb(20, 184, 166)
- gradient-orange-pink: rgb(249, 115, 22) -> rgb(236, 72, 153)
- gradient-red-purple: rgb(239, 68, 68) -> rgb(168, 85, 247)
- gradient-dark-gold: rgb(30, 41, 59) -> rgb(15, 23, 42)
- gradient-gold-amber: rgb(251, 191, 36) -> rgb(245, 158, 11)
- gradient-midnight: rgb(15, 23, 42) -> rgb(30, 58, 138)

## Typography
- Body: Inter, Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- Headings: Poppins, Inter, sans-serif
- Heading weight: 600

## Custom shadows and effects
- glow: 0 0 20px rgba(99, 102, 241, 0.4)
- glow-lg: 0 0 30px rgba(99, 102, 241, 0.6)
- chart shadow: 0 10px 40px rgba(99, 102, 241, 0.15)
- chart hover shadow: 0 15px 50px rgba(99, 102, 241, 0.25)
- glass-card bg: rgba(255, 255, 255, 0.7)
- glass-card border: rgba(255, 255, 255, 0.18)
- overlay: rgb(0 0 0 / 0.5) and rgba(0, 0, 0, 0.6)

## Layout constants
- Sidebar width: 280px
- Main content offset: 280px (desktop)
- Sidebar z-index: 10/40/50
- Overlay z-index: 30
- Common radii: 0.5rem, 0.75rem, 1rem, 1.5rem (from sidebar + utilities)

## Direct colors in custom CSS (non-tokenized)
- resources/css/app.css: none (all color values use tokens)
- resources/css/sidebar.css: none (all color values use tokens)

## Inline colors in Blade (sample list)
- resources/views/layouts/print.blade.php: #1d4ed8, #2563eb, #333, #666, #f0f0f0
- resources/views/components/*-chart.blade.php: rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.8)
- resources/views/errors/minimal.blade.php: multiple hard-coded grays and whites
- resources/views/welcome.blade.php: extensive palette of hex values

## Known duplicates/inconsistencies
- Inline colors remain in Blade views (print, charts, errors, welcome) and should be tokenized later.
