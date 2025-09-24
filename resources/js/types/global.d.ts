import "@vue/runtime-core";
import formatDate from "@/plugins/date-formatter";
import { formatCurrency } from "@/plugins/number-formatter";
import { router } from "@inertiajs/vue3";
import { Page, PageProps } from "@inertiajs/core";

declare module "@vue/runtime-core" {
    export interface ComponentCustomProperties {
        $inertia: typeof router;
        route: typeof route;
        $formatDate: typeof formatDate;
        $formatCurrency: typeof formatCurrency;
    }
}
