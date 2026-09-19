# Design system: Monochrome Precision

Minimalist, high-contrast, monochrome theme. Depth/emphasis via typographic
scale, hairline borders, and grayscale tonal shifts — no accent colors.

- Theme config: `src/plugins/vuetify.ts` (`monochromeTheme`).
- Tokens (spacing/radii/font-family CSS vars): `src/styles/tokens.css`.
- Fonts: Inter (UI text), JetBrains Mono (`.font-mono` — photographer
  credits, labels/tags/timers), loaded via Google Fonts in `index.html`.

## Typography

| Role                | Size / weight       | Vuetify class                          |
| ------------------- | ------------------- | -------------------------------------- |
| Page/hero heading   | 32–40px / 700       | `text-h4 font-weight-bold`             |
| Section title       | 20–24px / 700       | `text-h6 font-weight-bold`             |
| Card title          | 18–20px / 700       | `text-subtitle-1 font-weight-bold`     |
| Body                | 14–15px / 400       | `text-body-2`                          |
| Card meta/subtext   | 13–14px / 400       | `text-caption`                         |
| Photographer credit | 11–12px mono        | `font-mono text-caption text-disabled` |
| Labels/tags/timers  | 12px / 600, tracked | `text-overline font-weight-bold`       |

## Component patterns

- **App bar**: flat, `border="b"`, white background, wordmark left, cart
  pill (`N items • €total`) right. No secondary nav/avatar.
- **Event card**: outlined `v-card`, hairline divider between title block and
  logistics, full-width black CTA button (`from €X →`).
- **Quantity stepper**: bordered row, tier name + price left, `[-] qty [+]`
  right.
- **Cart expiry bar**: countdown text + `v-progress-linear` (black,
  determinate, counting down).
- **Primary CTA**: full-width black `v-btn`, `size="x-large"`, legal
  microcopy directly below.

## Layout

- Desktop container max-width `1440px`.
- Event grid: 4 cols desktop (`cols="12" sm="6" md="3"`), `24px` gutter,
  collapses to 1 col under `600px` with a sticky bottom CTA bar.

## Deviation from the reference mock

The reference mock shows a multi-provider payment picker (PayPal/Klarna/
card). Out of scope — [business-rules.md](../../docs/shared/business-rules.md#buy--checkout)
is mock checkout only, no payment gateway; the cart page's buy button posts
straight to `POST /cart/buy`.
