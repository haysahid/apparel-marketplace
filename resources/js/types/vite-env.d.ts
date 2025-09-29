/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_MIDTRANS_CLIENT_KEY: string;
    // add other env variables here
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
