- **Feature**: Page Templates now available (see README for more details)
- **Improvement**: Added REST and AJAX nonces to global shares
- **BugFix**: Documentation fixes

## Internal

- **Improvement**: Added Husky/Lint-Staged for pre-commit linting
- **Improvement**: Github Action caching for PHP dependencies and extensions
- **BugFix**: Remove `dist` build for now

## Breaking Change

The function signature for `inertiaResolvePage` has been changed.

Previously, the third argument was a `layoutCallback` function, now it is an `args` object

```js
createInertiaApp({
  resolve: resolveInertiaPage(
    import.meta.glob("./pages/**/*.vue", { eager: false }),
    DefaultLayout,
    {
      layoutCallback: (resolvedName, resolvedPage, resolvedTemplate) =>
        SomeLayout,
      templates: import.meta.glob("./templates/**/*.vue"),
    },
  ),
  // ...
});
```

Note that the third argument is optional still
