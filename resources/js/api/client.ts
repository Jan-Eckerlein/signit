import createClient from "openapi-fetch";
import type { paths } from "./openapi-schema";

const baseUrl = import.meta.env.VITE_APP_URL;

if (!baseUrl) {
    throw new Error("VITE_APP_URL is not set in .env");
}

export const client = createClient<paths>({
    baseUrl: `${baseUrl}`,
});
