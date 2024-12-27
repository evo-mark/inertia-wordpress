import { useForm, usePage } from "@inertiajs/vue3";

export const useAuthForm = (fields = {}) => {
	const form = useForm(fields);
	const originalSubmit = form.submit.bind(form);

	form.submit = async function (method = "post", url, options = {}, ...params) {
		if (method instanceof SubmitEvent) method = "post";
		options.preserveScroll ??= "errors";

		options.headers = Object.assign(options.headers ?? {}, {
			"X-WP-Nonce": usePage().props.wp.nonces.rest,
		});

		originalSubmit(method, url, options);
	};

	return form;
};
