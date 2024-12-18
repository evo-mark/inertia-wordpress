import { cloneDeep, transform, isEqual, isObject, debounce } from "lodash-es";
import { useApi } from "./useApi";
import { ref, watch, computed, nextTick } from "vue";

/**
 * @typedef {object} SettingsConfig
 * @property {string} url
 * @property {string} patchUrl
 * @property {string} deleteUrl
 * @property {number} debounce
 * @property {string[]} ignore
 */

/**
 * Deep diff between two object, using lodash
 * @param  {Object} object Object compared
 * @param  {Object} base   Object to compare with
 * @return {Object}        Return a new object who represent the diff
 */
function difference(object, base) {
	const changes = (object, base) => {
		return transform(object, (result, value, key) => {
			if (!isEqual(value, base[key])) {
				result[key] = isObject(value) && isObject(base[key]) ? changes(value, base[key]) : value;
			}
		});
	};
	return changes(object, base);
}

/**
 * @param {string[]} fields - An array of settings to sync with the server
 * @param {Partial<SettingsConfig>} config - A configuration object for the settings sync
 */
export const useSettings = (
	fields = [],
	{
		url = "settings",
		patchUrl,
		deleteUrl,
		debounce: debounceTime = 500,
		ignore = [],
		method = "GET",
		namespace = false,
		updateCallback = null,
		transform = null,
	} = {},
) => {
	method = method.toLowerCase();
	const hasTransform = typeof transform === "function";
	transform = hasTransform ? transform : (values) => values;
	const api = useApi();
	const isLoaded = ref(false);
	const pause = ref(false);
	const isDirty = ref(false);

	patchUrl ??= url;
	deleteUrl ??= url;
	fields = Array.isArray(fields) ? fields : [fields];

	/**
	 * @type {Ref<Record<string, any>>} settings
	 */
	const settings = ref({});

	const body = method === "get" ? { fields } : { fields, namespace };

	const refresh = () =>
		api[method](url, body).then((res) => {
			if (res.success !== true) {
				console.error(res);
				console.error("There was a problem");
				return false;
			}
			settings.value = transform(res.data?.settings ?? {});
			nextTick(() => {
				isLoaded.value = true;
			});
			return true;
		});

	const update = (fields) => {
		return api
			.patch(patchUrl, {
				fields,
			})
			.then(() => {
				isDirty.value = false;
				if (typeof updateCallback === "function") {
					updateCallback(fields);
				}
			});
	};
	const debouncedUpdate = debounce(update, debounceTime);

	const remove = (field) => {
		return api
			.delete(url, {
				params: {
					field,
				},
			})
			.then((res) => {
				settings.value[field] = res.data.data?.default;
			});
	};

	const _proxy = computed(() => cloneDeep(settings.value));
	watch(
		_proxy,
		(newValue, oldValue) => {
			if (isLoaded.value === true) {
				isDirty.value = true;
			}
			if (isLoaded.value === false || pause.value === true) return false;
			const diff = difference(newValue, oldValue);
			const changedKeys = Object.keys(diff).filter((d) => ignore.includes(d) === false);

			const fields = {};
			for (const key of changedKeys) {
				fields[key] = newValue[key];
			}

			const shouldIgnore = changedKeys?.length === 0;
			if (shouldIgnore === false) debouncedUpdate(fields);
		},
		{
			deep: true,
		},
	);

	const hydrated = refresh();

	return {
		settings,
		pause,
		refresh,
		update,
		debouncedUpdate,
		isDirty,
		remove,
		hydrated,
	};
};
