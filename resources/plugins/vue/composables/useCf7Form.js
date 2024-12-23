import { usePage, useForm } from "@inertiajs/vue3";
import { onUnmounted, ref } from "vue";
import { useIntersectionObserver, useScriptTag } from "@vueuse/core";
import {
  getRecaptchaToken,
  createFormDefaults,
  overrideFormMethods,
  removeRecaptchaFromWindow,
} from "../../shared/contactForm7";

export const useCf7Form = (name, config = {}) => {
  const formKey = config.formKey ?? null;
  const preloadRecaptcha = config.preloadRecaptcha ?? false;

  /* *********************************************
   * RECAPTCHA
   * ******************************************* */
  const recaptchaUrl = usePage().props?.cf7?.recaptchaUrl;
  const recaptchaSiteKey = usePage().props?.cf7?.recaptchaSiteKey;

  const { load: loadRecaptcha, unload: unloadRecaptcha } = recaptchaUrl
    ? useScriptTag(recaptchaUrl, undefined, {
        manual: preloadRecaptcha === false,
      })
    : { load: () => {}, unload: () => {} };

  const _unloadRecaptcha = () => {
    unloadRecaptcha();
    removeRecaptchaFromWindow();
  };

  const formRef = ref(null);
  const { stop } = useIntersectionObserver(
    formRef,
    ([{ isIntersecting }]) => {
      if (preloadRecaptcha) return false;

      if (isIntersecting) loadRecaptcha();
      else _unloadRecaptcha();
    },
    {
      rootMargin: "50%",
    },
  );

  onUnmounted(stop);

  /* *********************************************
   * OTHER
   * ******************************************* */

  const baseUrl = usePage().props.cf7?.restUrl ?? "";
  const forms = usePage().props.cf7?.forms ?? [];
  const cf7Form = forms.find((f) => f.name === name);
  if (!cf7Form) {
    console.error("Unable to find form " + name);
    return {};
  }

  const defaults = createFormDefaults(cf7Form);
  if (!recaptchaUrl) {
    delete defaults["_wpcf7_recaptcha_response"];
  }

  const _form = formKey ? useForm(formKey, defaults) : useForm(defaults);
  const submitUrl = `${baseUrl}contact-forms/${cf7Form.id}/feedback`;
  overrideFormMethods(_form, submitUrl, async (_form) => {
    _form._wpcf7_recaptcha_response = await getRecaptchaToken(recaptchaSiteKey);
  });

  return {
    form: _form,
    fields: Object.keys(defaults).filter(
      (field) => field.startsWith("_") === false,
    ),
    cf7Form,
    formRef,
    loadRecaptcha,
    unloadRecaptcha: _unloadRecaptcha,
  };
};
