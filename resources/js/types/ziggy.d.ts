import { route as routeFn } from "../../../vendor/tightenco/ziggy";

declare global {
    var route: typeof routeFn;
}
