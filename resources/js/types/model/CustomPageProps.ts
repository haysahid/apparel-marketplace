import { PageProps } from "@inertiajs/core";

export default interface CustomPageProps extends PageProps {
    flash?: {
        success?: string;
        [key: string]: any;
    };
    [key: string]: any;
}
