import { useForm, page } from "@inertiajs/svelte";
import {
  createFormDefaults,
  overrideFormMethods,
  getRecaptchaToken,
  removeRecaptchaFromWindow,
} from "../../shared/contactForm7";

const useScriptTag = (url, options = {}) => {
  const manual = options.manual;
  if (!url) {
    return {
      load: () => {},
      unload: () => {},
    };
  }

  let script;

  const load = () => {
    script = document.createElement("script");
    script.src = url;
    document.body.appendChild(script);
  };

  const unload = () => {
    if (!script) return;
    document.body.removeChild(script);
    script = null;
    removeRecaptchaFromWindow();
  };

  if (!manual) {
    load();
  }

  return { load, unload };
};

export const useCf7Form = (name, config = {}) => {
  const formKey = config.formKey ?? null;
  const preloadRecaptcha = config.preloadRecaptcha ?? false;

  let cf7;

  $: {
    page.subscribe((data) => {
      cf7 = data.props.cf7;
    });
  }

  const baseUrl = cf7?.restUrl ?? "";
  const recaptchaUrl = cf7?.recaptchaUrl;
  const recaptchaSiteKey = cf7?.recaptchaSiteKey;

  const forms = cf7?.forms ?? [];
  const cf7Form = forms.find((f) => f.name === name);
  if (!cf7Form) {
    console.error("Unable to find form " + name);
    return {};
  }

  const defaults = createFormDefaults(cf7Form);

  const _form = formKey ? useForm(formKey, defaults) : useForm(defaults);
  const submitUrl = `${baseUrl}contact-forms/${cf7Form.id}/feedback`;

  _form.subscribe((f) => {
    if (f._wp === true) return;
    const updatedForm = overrideFormMethods(f, submitUrl, async (_form) => {
      _form._wpcf7_recaptcha_response =
        await getRecaptchaToken(recaptchaSiteKey);
    });
    updatedForm._wp = true;
  });
  const { load: loadRecaptcha, unload: unloadRecaptcha } = useScriptTag(
    recaptchaUrl,
    {
      manual: preloadRecaptcha === false,
    },
  );

  const onMounted = (node) => {
    const observer = new IntersectionObserver(
      ([{ isIntersecting }]) => {
        if (preloadRecaptcha) return false;

        if (isIntersecting) loadRecaptcha();
        else unloadRecaptcha();
      },
      {
        rootMargin: "50%",
      },
    );
    observer.observe(node);

    $effect(() => {
      return () => {
        observer.disconnect();
        unloadRecaptcha();
      };
    });
  };

  return {
    form: _form,
    formRef: onMounted,
    fields: Object.keys(defaults).filter(
      (field) => field.startsWith("_") === false,
    ),
    cf7Form,
    loadRecaptcha,
    unloadRecaptcha,
  };
};
