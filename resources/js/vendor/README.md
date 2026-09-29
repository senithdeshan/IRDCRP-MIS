# Alpine fallback

`cdn.min.js` is the unmodified Alpine.js 3.15.12 browser distribution from
`https://registry.npmjs.org/alpinejs/-/alpinejs-3.15.12.tgz`, matching package-lock.json.
It is committed so a new checkout can initialize page controls even when the
Vite asset URL is unavailable. The layout only starts it when Alpine is absent
after deferred scripts have finished. Application components use their existing
source files, so their calculations are shared with the normal Vite bundle.

When upgrading Alpine, replace this distribution with the matching version.
Alpine.js is MIT licensed; see LICENSE.alpine.
