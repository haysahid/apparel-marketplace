interface SidebarMenuModel {
    name: string;
    icon?: string | null;
    active?: boolean | null;
    href?: string | null;
    roles?: string[];
    children?: SidebarMenuModel[];
}
