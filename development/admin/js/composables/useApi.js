const createBaseConfig = (headers = {}) => {
	return {
		baseURL: window.$inertia.restUrl,
		timeout: 60000, // Custom handling for timeout will be needed
		headers: {
			"X-WP-Nonce": window.$inertia.restNonce,
			...headers,
		},
	};
};

const baseConfig = createBaseConfig();

const fetchWithTimeout = (url, options, timeout = 60000) => {
	const controller = new AbortController();
	const signal = controller.signal;

	const timeoutId = setTimeout(() => controller.abort(), timeout);

	return fetch(url, { ...options, signal }).finally(() => clearTimeout(timeoutId));
};

/**
 * Fetch instance with pre-configured base options.
 */
export const useApi = () => {
	return {
		get: async (path, params = {}, headers = {}) => {
			const url = new URL(`${baseConfig.baseURL}${path}`);
			const searchParams = new URLSearchParams(params);
			const response = await fetchWithTimeout(
				url + "?" + searchParams,
				{
					method: "GET",
					headers: {
						...baseConfig.headers,
						...headers,
					},
					credentials: "include",
				},
				baseConfig.timeout,
			);

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			return response.json();
		},

		request: async (path, data, method, headers = {}) => {
			const url = `${baseConfig.baseURL}${path}`;
			const response = await fetchWithTimeout(
				url,
				{
					method,
					headers: {
						"Content-Type": "application/json",
						...baseConfig.headers,
						...headers,
					},
					body: JSON.stringify(data),
					credentials: "include",
				},
				baseConfig.timeout,
			);

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			return response.json();
		},

		post: async function (path, data, headers) {
			return this.request(path, data, "POST", headers);
		},

		patch: async function (path, data, headers) {
			return this.request(path, data, "PATCH", headers);
		},

		put: async function (path, data, headers) {
			return this.request(path, data, "PUT", headers);
		},
	};
};
