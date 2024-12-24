import { useForm, usePage } from "@inertiajs/react";
import {
	createFormDefaults,
	getRecaptchaToken,
	overrideFormMethods,
	removeRecaptchaFromWindow,
} from "../../shared/contactForm7";
import { useRef, useEffect } from "react";

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
	// Setup config
	const formKey = config.formKey ?? null;
	const preloadRecaptcha = config.preloadRecaptcha ?? false;

	let transform = (data) => data;
	const formRef = useRef(null);
	const recaptchaUrl = usePage().props?.cf7?.recaptchaUrl;
	const recaptchaSiteKey = usePage().props?.cf7?.recaptchaSiteKey;
	const baseUrl = usePage().props.cf7?.restUrl ?? "";

	const forms = usePage().props.cf7?.forms ?? [];
	const cf7Form = forms.find((f) => f.name === name);
	const submitUrl = `${baseUrl}contact-forms/${cf7Form.id}/feedback`;

	const defaults = createFormDefaults(cf7Form);
	if (!recaptchaUrl) {
		delete defaults["_wpcf7_recaptcha_response"];
	}

	const { load: loadRecaptcha, unload: unloadRecaptcha } = useScriptTag(recaptchaUrl, {
		manual: preloadRecaptcha === false,
	});

	const _form = formKey ? useForm(formKey, defaults) : useForm(defaults);
	overrideFormMethods(_form, submitUrl, async (_form) => {
		if (recaptchaSiteKey) {
			const token = await getRecaptchaToken(recaptchaSiteKey);
			_form.transform((data) => {
				data = {
					...data,
					_wpcf7_recaptcha_response: token,
				};
				return transform(data);
			});
		} else {
			_form.transform = transform;
		}
	});

	useEffect(() => {
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
		observer.observe(formRef.current);
	}, []);

	return {
		..._form,
		transform(callback) {
			transform = callback;
		},
		fields: Object.keys(defaults).filter((field) => field.startsWith("_") === false),
		formRef,
		loadRecaptcha,
		unloadRecaptcha,
	};
};
