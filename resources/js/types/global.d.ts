import "@vue/runtime-core";
import formatDate from "@/plugins/date-formatter";
import { formatCurrency, formatNumber } from "@/plugins/number-formatter";
import { router } from "@inertiajs/vue3";
import { Page } from "@inertiajs/core";
import CustomPageProps from "./model/CustomPageProps";
import { getImageUrl } from "@/plugins/helpers";

declare module "@vue/runtime-core" {
    export interface ComponentCustomProperties {
        $inertia: typeof router;
        $page: Page<CustomPageProps>;
        route: typeof route;
        $formatDate: typeof formatDate;
        $formatCurrency: typeof formatCurrency;
        $formatNumber: typeof formatNumber;
        $getImageUrl: typeof getImageUrl;
    }
}
