import { PageProps } from "@inertiajs/core";

export default interface CustomPageProps extends PageProps {
    previous_url?: string;
    auth: {
        user: UserEntity | null;
        is_admin: boolean | null;
        has_store: boolean | null;
        // Add other auth properties as needed
        [key: string]: any;
    };
    flash?: {
        success?: string;
        [key: string]: any;
    };
    selected_store_id: number | null;
    selected_store: StoreEntity | null;
    selected_store_role: RoleEntity | null;
    // Add other custom properties as needed
    [key: string]: any;
}
