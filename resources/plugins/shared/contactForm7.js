export const skippedProperties = [
  "submit",
  "isDirty",
  "errors",
  "hasErrors",
  "processing",
  "wasSuccessful",
  "recentlySuccessful",
  "__rememberable",
  "_url",
];

export const createFormDefaults = (cf7Form) => {
  return cf7Form.fields.reduce(
    (acc, curr) => {
      if (skippedProperties.includes(curr.type)) return acc;
      acc[curr.name] = curr.values[0] ?? "";
      return acc;
    },
    {
      _wpcf7_recaptcha_response: null,
      _wpcf7_unit_tag: `wpcf7-f-${cf7Form.id}-123`,
    },
  );
};

export const getRecaptchaToken = (key) =>
  new Promise((resolve, reject) => {
    if (typeof window === "undefined" || !window.grecaptcha)
      return reject("Inertia Wordpress: Recaptcha not found on window");

    if (!key)
      return reject("Inertia Wordpress: No Recaptcha site key set in CF7");

    window.grecaptcha.ready(function () {
      window.grecaptcha
        ?.execute(key, { action: "submit" })
        .then(function (token) {
          resolve(token);
        });
    });
  });

export const overrideFormMethods = (_form, submitUrl, captchaCallback) => {
  const originalSubmit = _form.submit.bind(_form);

  _form.submit = async function (method = "post", options = {}, ...params) {
    if (method instanceof SubmitEvent) method = "post";
    options.forceFormData = true;
    options.preserveScroll ??= "errors";

    await captchaCallback(_form);

    originalSubmit(method, submitUrl, options);
  };
  _form.get = function (options) {
    this.submit("get", options);
  };
  _form.post = function (options) {
    this.submit("post", options);
  };
  _form.put = function (options) {
    this.submit("put", options);
  };
  _form.patch = function (options) {
    this.submit("patch", options);
  };
  _form.delete = function (options) {
    this.submit("delete", options);
  };
  _form._url = submitUrl;
  return _form;
};

export const removeRecaptchaFromWindow = () => {
  const badges = document.querySelectorAll(".grecaptcha-badge");
  badges.forEach((badge) => {
    if (badge?.parentElement) badge.parentElement.remove();
  });

  if (window.grecaptcha) delete window.grecaptcha;
  if (window.___grecaptcha_cfg) delete window.___grecaptcha_cfg;
};
